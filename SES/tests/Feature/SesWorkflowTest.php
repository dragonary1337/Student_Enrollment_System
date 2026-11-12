<?php

use App\Models\Department;
use App\Models\PaymentMethod;
use App\Models\Program;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\PaymentService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

test('reporting views can be queried after migration', function () {
    foreach ([
        'v_student_details',
        'v_section_availability',
        'v_enrollment_details',
        'v_payment_history',
        'v_student_term_summary',
        'v_user_accounts',
    ] as $view) {
        expect(DB::table($view)->limit(1)->get())->toBeInstanceOf(Collection::class);
    }
});

function makeSesStudent(string $studentNo, ?int $programId = null): Student
{
    return Student::query()->create([
        'student_no' => $studentNo,
        'first_name' => 'Sam',
        'last_name' => 'Student',
        'program_id' => $programId,
        'year_level' => 1,
        'status' => 'Active',
    ]);
}

function makeSesSection(string $code, int $subjectId, int $capacity = 10, string $year = '2025-2026', string $semester = '1st'): Section
{
    return Section::query()->create([
        'section_code' => $code,
        'subject_id' => $subjectId,
        'school_year' => $year,
        'semester' => $semester,
        'max_capacity' => $capacity,
    ]);
}

test('active users can sign in using their username', function () {
    $user = User::factory()->create(['username' => 'ses-operator']);

    $this->post(route('login.store'), [
        'email' => 'SES-OPERATOR',
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('public landing page introduces SES and provides account actions', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Student Enrollment System')
        ->assertSee(route('login'))
        ->assertSee(route('register'));
});

test('pending accounts cannot sign in and operators cannot access academic setup', function () {
    $pending = User::factory()->create(['status' => 'Pending']);

    $this->post(route('login.store'), [
        'email' => $pending->email,
        'password' => 'password',
    ])->assertSessionHasErrors([
        'email' => 'Your account is awaiting administrator approval. Contact the system administrator to activate it.',
    ]);

    $operator = User::factory()->create(['role' => 'Operator']);
    $this->actingAs($operator)->get(route('departments.index'))->assertForbidden();
    $this->actingAs($operator)->get(route('students.index'))->assertOk();
});

test('drop and add workflow is not available in the milestone baseline', function () {
    $operator = User::factory()->create(['role' => 'Operator']);

    $this->actingAs($operator)
        ->post('/enrollments/1/drop-add', ['section_id' => 2])
        ->assertNotFound();
});

test('administrators can create students and invalid year levels are rejected', function () {
    $admin = User::factory()->create();
    $department = Department::query()->create([
        'department_code' => 'SCI',
        'department_name' => 'Science',
    ]);
    $program = Program::query()->create([
        'program_code' => 'BSBIO',
        'program_name' => 'Biology',
        'department_id' => $department->department_id,
    ]);

    $this->actingAs($admin)->post(route('students.store'), [
        'student_no' => 'S-1001',
        'first_name' => 'Riley',
        'last_name' => 'Green',
        'program_id' => $program->program_id,
        'year_level' => 2,
        'status' => 'Active',
    ])->assertRedirect(route('students.index'));

    $this->assertDatabaseHas('students', ['student_no' => 'S-1001', 'program_id' => $program->program_id]);
    $this->actingAs($admin)->get(route('students.show', Student::query()->where('student_no', 'S-1001')->value('student_id')))
        ->assertOk()
        ->assertSee('Riley');

    $this->actingAs($admin)->from(route('students.create'))->post(route('students.store'), [
        'student_no' => 'S-1002',
        'first_name' => 'Invalid',
        'last_name' => 'Year',
        'year_level' => 8,
        'status' => 'Active',
    ])->assertSessionHasErrors('year_level');
});

test('the last active administrator cannot be demoted', function () {
    $admin = User::factory()->create(['role' => 'Admin', 'status' => 'Active']);

    $this->actingAs($admin)->patch(route('users.update', $admin), [
        'role' => 'Operator',
        'status' => 'Active',
    ])->assertSessionHasErrors('status');

    $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'Admin', 'status' => 'Active']);
});

test('enrollment honors capacity and active enrollments can be dropped', function () {
    $operator = User::factory()->create(['role' => 'Operator']);
    $subject = Subject::query()->create(['subject_code' => 'BIO101', 'subject_name' => 'Biology', 'units' => 3]);
    $fullSection = makeSesSection('BIO-A', $subject->subject_id, 1);
    $student = makeSesStudent('S-2001');
    $anotherStudent = makeSesStudent('S-2002');
    $service = app(EnrollmentService::class);

    $original = $service->enroll($student->student_id, $fullSection->section_id, $operator->id);

    expect(fn () => $service->enroll($anotherStudent->student_id, $fullSection->section_id, $operator->id))
        ->toThrow(ValidationException::class);

    $dropped = $service->drop($original->enrollment_id, $operator->id);

    expect($dropped->status)->toBe('Dropped');
    $this->assertDatabaseHas('enrollments', ['enrollment_id' => $original->enrollment_id, 'status' => 'Dropped']);
});

test('section capacity cannot be reduced below current enrollment', function () {
    $admin = User::factory()->create();
    $subject = Subject::query()->create(['subject_code' => 'HIS101', 'subject_name' => 'History', 'units' => 3]);
    $section = makeSesSection('HIS-A', $subject->subject_id, 2);
    $student = makeSesStudent('S-2501');
    $anotherStudent = makeSesStudent('S-2502');
    app(EnrollmentService::class)->enroll($student->student_id, $section->section_id, $admin->id);
    app(EnrollmentService::class)->enroll($anotherStudent->student_id, $section->section_id, $admin->id);

    $this->actingAs($admin)->put(route('sections.update', $section->section_id), [
        'section_code' => 'HIS-A',
        'subject_id' => $subject->subject_id,
        'school_year' => '2025-2026',
        'semester' => '1st',
        'max_capacity' => 1,
    ])->assertSessionHasErrors('max_capacity');

    $this->assertDatabaseHas('sections', ['section_id' => $section->section_id, 'max_capacity' => 2]);
});

test('payments require an active enrollment in the same term and an available method', function () {
    $operator = User::factory()->create(['role' => 'Operator']);
    $student = makeSesStudent('S-3001');
    $subject = Subject::query()->create(['subject_code' => 'ENG101', 'subject_name' => 'English', 'units' => 3]);
    $section = makeSesSection('ENG-A', $subject->subject_id);
    app(EnrollmentService::class)->enroll($student->student_id, $section->section_id, $operator->id);
    $method = PaymentMethod::query()->create(['method_name' => 'Cash', 'method_available' => true]);
    $service = app(PaymentService::class);

    $payment = $service->record([
        'student_id' => $student->student_id,
        'school_year' => '2025-2026',
        'semester' => '1st',
        'amount' => '1250.00',
        'payment_method_id' => $method->payment_method_id,
    ], $operator->id);

    expect((float) $payment->amount)->toBe(1250.0);

    $method->update(['method_available' => false]);
    expect(fn () => $service->record([
        'student_id' => $student->student_id,
        'school_year' => '2025-2026',
        'semester' => '1st',
        'amount' => '500.00',
        'payment_method_id' => $method->payment_method_id,
    ], $operator->id))->toThrow(ValidationException::class);
});

test('reports display database-backed student rows and support csv export', function () {
    $admin = User::factory()->create();
    makeSesStudent('S-4001');

    $this->actingAs($admin)->get(route('reports.index', ['type' => 'students']))
        ->assertOk()
        ->assertSee('S-4001');

    $this->actingAs($admin)->get(route('reports.export', ['type' => 'students']))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});
