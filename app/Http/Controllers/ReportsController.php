<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;


class ReportsController extends Controller
{
    private const TYPES = ['students', 'enrollments', 'sections', 'payments'];

    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        [$title, $headers, $rows] = $this->reportData($filters);

        return view('reports.index', compact('filters', 'title', 'headers', 'rows'));
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        [$title, $headers, $rows] = $this->reportData($filters);

        return response()->streamDownload(function () use ($headers, $rows): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, $headers);

            foreach ($rows as $row) {
                fputcsv($output, array_map($this->spreadsheetSafe(...), $row));
            }

            fclose($output);
        }, Str::slug($title).'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'type' => ['nullable', 'in:'.implode(',', self::TYPES)],
            'status' => ['nullable', 'string', 'max:20'],
            'school_year' => ['nullable', 'string', 'size:9'],
            'semester' => ['nullable', 'in:1st,2nd,Summer'],
        ]) + ['type' => $request->query('type', 'students')];
    }

    private function reportData(array $filters): array
    {
        $type = $filters['type'];

        if ($type === 'students') {
            $query = Student::query()->with('program');
            if ($filters['status'] ?? null) {
                $query->where('status', $filters['status']);
            }

            $rows = $query->orderBy('student_no')->get()->map(fn (Student $student) => [
                $student->student_no,
                $student->full_name,
                $student->program?->program_code ?? '',
                $student->year_level,
                $student->status,
                $student->email,
            ])->all();

            return ['Student Report', ['Student no.', 'Name', 'Program', 'Year', 'Status', 'Email'], $rows];
        }

        if ($type === 'enrollments') {
            $query = Enrollment::query()->with(['student', 'section.subject']);
            $this->applyTermFilters($query, $filters, 'section');
            if ($filters['status'] ?? null) {
                $query->where('status', $filters['status']);
            }

            $rows = $query->orderByDesc('enrollment_datetime')->get()->map(fn (Enrollment $enrollment) => [
                $enrollment->student?->student_no ?? '',
                $enrollment->student?->full_name ?? '',
                $enrollment->section?->section_code ?? '',
                $enrollment->section?->subject?->subject_code ?? '',
                $enrollment->section?->school_year ?? '',
                $enrollment->section?->semester ?? '',
                $enrollment->status,
                $enrollment->enrollment_datetime?->toDateTimeString() ?? '',
            ])->all();

            return ['Enrollment Report', ['Student no.', 'Student', 'Section', 'Subject', 'School year', 'Semester', 'Status', 'Enrolled at'], $rows];
        }

        if ($type === 'sections') {
            $query = Section::query()->with(['subject', 'instructor'])->withCount([
                'enrollments as enrolled_count' => fn ($enrollments) => $enrollments->where('status', 'Enrolled'),
            ]);
            $this->applyTermFilters($query, $filters);

            $rows = $query->orderBy('school_year')->orderBy('section_code')->get()->map(fn (Section $section) => [
                $section->section_code,
                $section->subject?->subject_code ?? '',
                $section->instructor?->last_name ?? '',
                $section->school_year,
                $section->semester,
                $section->enrolled_count,
                $section->max_capacity,
                max(0, $section->max_capacity - $section->enrolled_count),
            ])->all();

            return ['Section Availability Report', ['Section', 'Subject', 'Instructor', 'School year', 'Semester', 'Enrolled', 'Capacity', 'Available slots'], $rows];
        }

        $query = Payment::query()->with(['student', 'paymentMethod']);
        $this->applyTermFilters($query, $filters);
        $rows = $query->orderByDesc('payment_datetime')->get()->map(fn (Payment $payment) => [
            $payment->student?->student_no ?? '',
            $payment->student?->full_name ?? '',
            $payment->school_year,
            $payment->semester,
            $payment->amount,
            $payment->paymentMethod?->method_name ?? '',
            $payment->payment_datetime?->toDateTimeString() ?? '',
        ])->all();

        return ['Payment Report', ['Student no.', 'Student', 'School year', 'Semester', 'Amount', 'Payment method', 'Paid at'], $rows];
    }

    private function applyTermFilters($query, array $filters, ?string $relation = null): void
    {
        foreach (['school_year', 'semester'] as $filter) {
            if (! empty($filters[$filter])) {
                $relation === null
                    ? $query->where($filter, $filters[$filter])
                    : $query->whereHas($relation, fn ($related) => $related->where($filter, $filters[$filter]));
            }
        }
    }

    private function spreadsheetSafe(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^[\s]*[=+\-@]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
