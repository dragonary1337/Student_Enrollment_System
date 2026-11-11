<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function record(array $data, int $userId): Payment
    {
        return DB::transaction(function () use ($data, $userId): Payment {
            $student = Student::query()->lockForUpdate()->findOrFail($data['student_id']);
            $method = PaymentMethod::query()->lockForUpdate()->findOrFail($data['payment_method_id']);

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
                ->lockForUpdate()
                ->first();

            if ($termEnrollment === null) {
                throw ValidationException::withMessages(['student_id' => 'The student has no active enrollment for the selected term.']);
            }

            return Payment::query()->create([
                ...$data,
                'recorded_by' => $userId,
            ]);
        }, attempts: 3);
    }
}
