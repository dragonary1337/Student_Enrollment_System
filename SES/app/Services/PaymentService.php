<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Student;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * @param  array{student_id: int, school_year: string, semester: string, amount: string, payment_method_id: int}  $data
     */
    public function record(array $data, int $userId): Payment
    {
        $student = Student::query()->findOrFail((int) $data['student_id']);
        $method = PaymentMethod::query()->findOrFail((int) $data['payment_method_id']);

        if ($student->status !== 'Active') {
            throw ValidationException::withMessages(['student_id' => 'Payments can only be recorded for active students.']);
        }

        if (! $method->method_available) {
            throw ValidationException::withMessages(['payment_method_id' => 'This payment method is currently unavailable.']);
        }

        $termEnrollment = $student->enrollments()
            ->where('status', 'Enrolled')
            ->whereHas('section', fn ($query) => $query
                ->where('school_year', $data['school_year'])
                ->where('semester', $data['semester']))
            ->first();

        if ($termEnrollment === null) {
            throw ValidationException::withMessages(['student_id' => 'The student has no active enrollment for the selected term.']);
        }

        return Payment::query()->create([
            ...$data,
            'recorded_by' => $userId,
        ]);
    }
}
