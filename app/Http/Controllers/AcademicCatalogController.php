<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Instructor;
use App\Models\PaymentMethod;
use App\Models\Program;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AcademicCatalogController extends Controller
{
    private static function catalogs(): array
    {
        return [
            'departments' => [
                'model' => Department::class,
                'title' => 'Departments',
                'singular' => 'Department',
                'search' => ['department_code', 'department_name'],
                'with' => [],
                'columns' => ['department_code' => 'Code', 'department_name' => 'Department'],
                'blockedBy' => [['programs', 'department_id'], ['subjects', 'department_id'], ['instructors', 'department_id']],
                'fields' => [
                    'department_code' => ['label' => 'Department code', 'rules' => ['required', 'string', 'max:10'], 'unique' => true],
                    'department_name' => ['label' => 'Department name', 'rules' => ['required', 'string', 'max:100'], 'unique' => true],
                ],
            ],
            'programs' => [
                'model' => Program::class,
                'title' => 'Programs',
                'singular' => 'Program',
                'search' => ['program_code', 'program_name'],
                'with' => ['department'],
                'columns' => ['program_code' => 'Code', 'program_name' => 'Program', 'department.department_code' => 'Department'],
                'blockedBy' => [['students', 'program_id']],
                'fields' => [
                    'program_code' => ['label' => 'Program code', 'rules' => ['required', 'string', 'max:10'], 'unique' => true],
                    'program_name' => ['label' => 'Program name', 'rules' => ['required', 'string', 'max:100'], 'unique' => true],
                    'department_id' => ['label' => 'Department', 'rules' => ['required', 'integer', 'exists:departments,department_id'], 'relation' => [Department::class, 'department_id', 'department_name']],
                ],
            ],
            'subjects' => [
                'model' => Subject::class,
                'title' => 'Subjects',
                'singular' => 'Subject',
                'search' => ['subject_code', 'subject_name'],
                'with' => ['department'],
                'columns' => ['subject_code' => 'Code', 'subject_name' => 'Subject', 'units' => 'Units', 'department.department_code' => 'Department'],
                'blockedBy' => [['sections', 'subject_id']],
                'fields' => [
                    'subject_code' => ['label' => 'Subject code', 'rules' => ['required', 'string', 'max:10'], 'unique' => true],
                    'subject_name' => ['label' => 'Subject name', 'rules' => ['required', 'string', 'max:100']],
                    'units' => ['label' => 'Units', 'type' => 'number', 'rules' => ['required', 'numeric', 'gt:0', 'max:9.9'], 'step' => '0.1'],
                    'department_id' => ['label' => 'Department', 'rules' => ['nullable', 'integer', 'exists:departments,department_id'], 'relation' => [Department::class, 'department_id', 'department_name']],
                ],
            ],
            'instructors' => [
                'model' => Instructor::class,
                'title' => 'Instructors',
                'singular' => 'Instructor',
                'search' => ['first_name', 'last_name', 'email'],
                'with' => ['department'],
                'columns' => ['first_name' => 'First name', 'last_name' => 'Last name', 'email' => 'Email', 'department.department_code' => 'Department'],
                'blockedBy' => [['sections', 'instructor_id']],
                'fields' => [
                    'first_name' => ['label' => 'First name', 'rules' => ['required', 'string', 'max:50']],
                    'middle_name' => ['label' => 'Middle name', 'rules' => ['nullable', 'string', 'max:50']],
                    'last_name' => ['label' => 'Last name', 'rules' => ['required', 'string', 'max:50']],
                    'email' => ['label' => 'Email', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:100'], 'unique' => true],
                    'department_id' => ['label' => 'Department', 'rules' => ['nullable', 'integer', 'exists:departments,department_id'], 'relation' => [Department::class, 'department_id', 'department_name']],
                ],
            ],
            'students' => [
                'model' => Student::class,
                'title' => 'Students',
                'singular' => 'Student',
                'search' => ['student_no', 'first_name', 'last_name', 'email'],
                'with' => ['program'],
                'columns' => ['student_no' => 'Student no.', 'full_name' => 'Student', 'program.program_code' => 'Program', 'year_level' => 'Year', 'status' => 'Status'],
                'blockedBy' => [['enrollments', 'student_id'], ['payments', 'student_id']],
                'fields' => [
                    'student_no' => ['label' => 'Student number', 'rules' => ['required', 'string', 'max:10'], 'unique' => true],
                    'first_name' => ['label' => 'First name', 'rules' => ['required', 'string', 'max:50']],
                    'middle_name' => ['label' => 'Middle name', 'rules' => ['nullable', 'string', 'max:50']],
                    'last_name' => ['label' => 'Last name', 'rules' => ['required', 'string', 'max:50']],
                    'gender' => ['label' => 'Gender', 'rules' => ['nullable', Rule::in(['Male', 'Female', 'Other'])], 'options' => ['Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other']],
                    'email' => ['label' => 'Email', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:100'], 'unique' => true],
                    'contact_no' => ['label' => 'Contact number', 'rules' => ['nullable', 'string', 'max:20']],
                    'program_id' => ['label' => 'Program', 'rules' => ['nullable', 'integer', 'exists:programs,program_id'], 'relation' => [Program::class, 'program_id', 'program_code']],
                    'year_level' => ['label' => 'Year level', 'type' => 'number', 'rules' => ['required', 'integer', 'between:1,5']],
                    'status' => ['label' => 'Status', 'rules' => ['required', Rule::in(['Active', 'Inactive', 'Graduated', 'Dropped'])], 'options' => ['Active' => 'Active', 'Inactive' => 'Inactive', 'Graduated' => 'Graduated', 'Dropped' => 'Dropped']],
                ],
            ],
            'sections' => [
                'model' => Section::class,
                'title' => 'Sections',
                'singular' => 'Section',
                'search' => ['section_code', 'school_year', 'room'],
                'with' => ['subject', 'instructor'],
                'columns' => ['section_code' => 'Section', 'subject.subject_code' => 'Subject', 'instructor.last_name' => 'Instructor', 'school_year' => 'School year', 'semester' => 'Semester', 'max_capacity' => 'Capacity'],
                'blockedBy' => [['enrollments', 'section_id']],
                'fields' => [
                    'section_code' => ['label' => 'Section code', 'rules' => ['required', 'string', 'max:10']],
                    'subject_id' => ['label' => 'Subject', 'rules' => ['required', 'integer', 'exists:subjects,subject_id'], 'relation' => [Subject::class, 'subject_id', 'subject_code']],
                    'instructor_id' => ['label' => 'Instructor', 'rules' => ['nullable', 'integer', 'exists:instructors,instructor_id'], 'relation' => [Instructor::class, 'instructor_id', 'last_name']],
                    'school_year' => ['label' => 'School year', 'rules' => ['required', 'string', 'size:9']],
                    'semester' => ['label' => 'Semester', 'rules' => ['required', Rule::in(['1st', '2nd', 'Summer'])], 'options' => ['1st' => '1st semester', '2nd' => '2nd semester', 'Summer' => 'Summer']],
                    'schedule_day' => ['label' => 'Schedule days', 'rules' => ['nullable', 'string', 'max:10']],
                    'start_time' => ['label' => 'Start time', 'type' => 'time', 'rules' => ['nullable', 'date_format:H:i']],
                    'end_time' => ['label' => 'End time', 'type' => 'time', 'rules' => ['nullable', 'date_format:H:i', 'after:start_time']],
                    'room' => ['label' => 'Room', 'rules' => ['nullable', 'string', 'max:10']],
                    'max_capacity' => ['label' => 'Maximum capacity', 'type' => 'number', 'rules' => ['required', 'integer', 'min:1']],
                ],
            ],
            'payment-methods' => [
                'model' => PaymentMethod::class,
                'title' => 'Payment methods',
                'singular' => 'Payment method',
                'search' => ['method_name'],
                'with' => [],
                'columns' => ['method_name' => 'Payment method', 'method_available' => 'Available'],
                'blockedBy' => [['payments', 'payment_method_id']],
                'fields' => [
                    'method_name' => ['label' => 'Method name', 'rules' => ['required', 'string', 'max:50'], 'unique' => true],
                    'method_available' => ['label' => 'Availability', 'rules' => ['required', 'boolean'], 'options' => ['1' => 'Available', '0' => 'Unavailable']],
                ],
            ],
        ];
    }

    public function index(Request $request): View
    {
        $catalog = $this->catalog($request);
        $modelClass = $catalog['model'];
        $query = $modelClass::query()->with($catalog['with']);

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($nested) use ($catalog, $search): void {
                foreach ($catalog['search'] as $column) {
                    $nested->orWhere($column, 'like', '%'.$search.'%');
                }
            });
        }

        return view('catalog.index', [
            'catalog' => $catalog,
            'records' => $query->latest($modelClass === Student::class ? 'date_registered' : $modelClass::make()->getKeyName())->paginate(15)->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        return $this->formView($request, null);
    }

    public function store(Request $request): RedirectResponse
    {
        $catalog = $this->catalog($request);
        $data = $this->validated($request, $catalog);
        $catalog['model']::query()->create($data);

        return redirect()->route($request->route('catalog').'.index')
            ->with('success', $catalog['singular'].' created successfully.');
    }

    public function edit(Request $request): View
    {
        return $this->formView($request, $this->record($request));
    }

    public function show(Request $request): View
    {
        return view('catalog.show', [
            'catalog' => $this->catalog($request),
            'record' => $this->record($request),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $catalog = $this->catalog($request);
        $record = $this->record($request);
        $data = $this->validated($request, $catalog, $record);

        if ($request->route('catalog') === 'sections') {
            $enrolledCount = $record->enrollments()->where('status', 'Enrolled')->count();
            if ((int) $data['max_capacity'] < $enrolledCount) {
                throw ValidationException::withMessages([
                    'max_capacity' => 'Capacity cannot be lower than the current enrolled student count.',
                ]);
            }
        }

        $record->update($data);

        return redirect()->route($request->route('catalog').'.index')
            ->with('success', $catalog['singular'].' updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $catalog = $this->catalog($request);
        $record = $this->record($request);

        foreach ($catalog['blockedBy'] as [$table, $foreignKey]) {
            if (DB::table($table)->where($foreignKey, $record->getKey())->exists()) {
                return back()->with('error', 'This record is referenced by existing '.$table.'. Update those records before deleting it.');
            }
        }

        $record->delete();

        return redirect()->route($request->route('catalog').'.index')
            ->with('success', $catalog['singular'].' deleted successfully.');
    }

    private function formView(Request $request, ?Model $record): View
    {
        $catalog = $this->catalog($request);

        foreach ($catalog['fields'] as $name => &$field) {
            if (isset($field['relation'])) {
                [$related, $key, $label] = $field['relation'];
                $field['options'] = $related::query()->orderBy($label)->pluck($label, $key)->all();
            }
        }
        unset($field);

        return view('catalog.form', compact('catalog', 'record'));
    }

    private function validated(Request $request, array $catalog, ?Model $record = null): array
    {
        $rules = [];

        foreach ($catalog['fields'] as $name => $field) {
            $rules[$name] = $field['rules'];

            if (($field['unique'] ?? false) === true) {
                $unique = Rule::unique($catalog['model']::make()->getTable(), $name);
                if ($record !== null) {
                    $unique->ignore($record->getKey(), $record->getKeyName());
                }
                $rules[$name][] = $unique;
            }
        }

        return $request->validate($rules);
    }

    private function record(Request $request): Model
    {
        $modelClass = $this->catalog($request)['model'];

        return $modelClass::query()->findOrFail($request->route('record'));
    }

    private function catalog(Request $request): array
    {
        return self::catalogs()[$request->route('catalog')] ?? abort(404);
    }
}
