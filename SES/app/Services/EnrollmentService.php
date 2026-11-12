<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Validation\ValidationException;

class EnrollmentService
{
    public function enroll(int $studentId, int $sectionId, int $userId): Enrollment
    {
        $student = Student::query()->findOrFail($studentId);
        $section = Section::query()->findOrFail($sectionId);

        if ($student->status !== 'Active') {
            throw ValidationException::withMessages(['student_id' => 'Only active students may enroll.']);
        }

        $existing = Enrollment::query()
            ->where('student_id', $studentId)
            ->where('section_id', $sectionId)
            ->first();

        if ($existing?->status === 'Enrolled') {
            throw ValidationException::withMessages(['section_id' => 'This student is already enrolled in the section.']);
        }

        $enrolled = $section->enrollments()->where('status', 'Enrolled')->count();

        if ($enrolled >= $section->max_capacity) {
            throw ValidationException::withMessages(['section_id' => 'The selected section is full.']);
        }

        if ($existing !== null) {
            $existing->update([
                'status' => 'Enrolled',
                'enrollment_datetime' => now(),
                'processed_by' => $userId,
            ]);

            return $existing->refresh();
        }

        return Enrollment::query()->create([
            'student_id' => $studentId,
            'section_id' => $sectionId,
            'status' => 'Enrolled',
            'processed_by' => $userId,
        ]);
    }

    public function drop(int $enrollmentId, int $userId): Enrollment
    {
        $enrollment = Enrollment::query()->findOrFail($enrollmentId);

        if ($enrollment->status !== 'Enrolled') {
            throw ValidationException::withMessages(['enrollment_id' => 'Only an active enrollment can be dropped.']);
        }

        $enrollment->update([
            'status' => 'Dropped',
            'processed_by' => $userId,
        ]);

        return $enrollment->refresh();
    }
}
