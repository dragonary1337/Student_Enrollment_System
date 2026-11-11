<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\PaymentMethod;
use App\Models\Program;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::query()->firstOrCreate(
            ['department_code' => 'CCS'],
            ['department_name' => 'College of Computer Studies'],
        );
        $program = Program::query()->firstOrCreate(
            ['program_code' => 'BSIT'],
            ['program_name' => 'Bachelor of Science in Information Technology', 'department_id' => $department->department_id],
        );
        $subject = Subject::query()->firstOrCreate(
            ['subject_code' => 'IT101'],
            ['subject_name' => 'Introduction to Information Technology', 'units' => 3, 'department_id' => $department->department_id],
        );
        $instructor = Instructor::query()->firstOrCreate(
            ['email' => 'instructor.demo@example.test'],
            ['first_name' => 'Alex', 'last_name' => 'Instructor', 'department_id' => $department->department_id],
        );
        PaymentMethod::query()->firstOrCreate(['method_name' => 'Cash'], ['method_available' => true]);
        PaymentMethod::query()->firstOrCreate(['method_name' => 'Bank transfer'], ['method_available' => true]);

        $student = Student::query()->firstOrCreate(
            ['student_no' => 'SES-2025-001'],
            [
                'first_name' => 'Jamie',
                'last_name' => 'Santos',
                'email' => 'jamie.santos@example.test',
                'program_id' => $program->program_id,
                'year_level' => 1,
                'status' => 'Active',
            ],
        );
        $section = Section::query()->firstOrCreate(
            ['section_code' => 'IT101-A', 'school_year' => '2025-2026', 'semester' => '1st'],
            [
                'subject_id' => $subject->subject_id,
                'instructor_id' => $instructor->instructor_id,
                'schedule_day' => 'MWF',
                'start_time' => '08:00:00',
                'end_time' => '09:00:00',
                'room' => 'LAB-1',
                'max_capacity' => 40,
            ],
        );

        Enrollment::query()->firstOrCreate(
            ['student_id' => $student->student_id, 'section_id' => $section->section_id],
            ['status' => 'Enrolled'],
        );
    }
}
