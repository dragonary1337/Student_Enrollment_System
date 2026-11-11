<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentService
{
    public function enroll(int $studentId, int $sectionId, int $userId): Enrollment
    {
        return DB::transaction(function () use ($studentId, $sectionId, $userId): Enrollment {
            $student = Student::query()->lockForUpdate()->findOrFail($studentId);
            $section = Section::query()->lockForUpdate()->findOrFail($sectionId);

            if ($student->status !== 'Active') {
                throw ValidationException::withMessages(['student_id' => 'Only active students may enroll.']);
            }

            $existing = Enrollment::query()
                ->where('student_id', $studentId)
                ->where('section_id', $sectionId)
                ->lockForUpdate()
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
        }, attempts: 3);
    }

    public function dropAndAdd(int $enrollmentId, int $newSectionId, int $userId): Enrollment
    {
        return DB::transaction(function () use ($enrollmentId, $newSectionId, $userId): Enrollment {
            $oldEnrollment = Enrollment::query()->lockForUpdate()->findOrFail($enrollmentId);

            if ($oldEnrollment->status !== 'Enrolled') {
                throw ValidationException::withMessages(['enrollment_id' => 'Only an active enrollment can be dropped.']);
            }

            $student = Student::query()->lockForUpdate()->findOrFail($oldEnrollment->student_id);
            if ($student->status !== 'Active') {
                throw ValidationException::withMessages(['enrollment_id' => 'Only active students may be moved to another section.']);
            }

            if ((int) $oldEnrollment->section_id === $newSectionId) {
                throw ValidationException::withMessages(['section_id' => 'Choose a different section for the add operation.']);
            }

            $newSection = Section::query()->lockForUpdate()->findOrFail($newSectionId);

            if ($newSection->school_year !== $oldEnrollment->section()->value('school_year')
                || $newSection->semester !== $oldEnrollment->section()->value('semester')) {
                throw ValidationException::withMessages([
                    'section_id' => 'Drop/add must be completed within the same school year and semester.',
                ]);
            }

            $existing = Enrollment::query()
                ->where('student_id', $oldEnrollment->student_id)
                ->where('section_id', $newSectionId)
                ->lockForUpdate()
                ->first();

            if ($existing?->status === 'Enrolled') {
                throw ValidationException::withMessages(['section_id' => 'The student is already enrolled in the selected section.']);
            }

            $enrolled = $newSection->enrollments()->where('status', 'Enrolled')->count();

            if ($enrolled >= $newSection->max_capacity) {
                throw ValidationException::withMessages(['section_id' => 'The selected section is full. No changes were made.']);
            }

            $oldEnrollment->update([
                'status' => 'Dropped',
                'processed_by' => $userId,
            ]);

            if ($existing !== null) {
                $existing->update([
                    'status' => 'Enrolled',
                    'enrollment_datetime' => now(),
                    'processed_by' => $userId,
                ]);

                return $existing->refresh();
            }

            return Enrollment::query()->create([
                'student_id' => $oldEnrollment->student_id,
                'section_id' => $newSectionId,
                'status' => 'Enrolled',
                'processed_by' => $userId,
            ]);
        }, attempts: 3);
    }

    public function drop(int $enrollmentId, int $userId): Enrollment
    {
        return DB::transaction(function () use ($enrollmentId, $userId): Enrollment {
            $enrollment = Enrollment::query()->lockForUpdate()->findOrFail($enrollmentId);

            if ($enrollment->status !== 'Enrolled') {
                throw ValidationException::withMessages(['enrollment_id' => 'Only an active enrollment can be dropped.']);
            }

            $enrollment->update([
                'status' => 'Dropped',
                'processed_by' => $userId,
            ]);

            return $enrollment->refresh();
        }, attempts: 3);
    }
}
