<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Student;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Enrollment::query()->with(['student.program', 'section.subject', 'processor']);

        if ($request->filled('status') && in_array($request->query('status'), ['Enrolled', 'Dropped', 'Completed'], true)) {
            $query->where('status', $request->query('status'));
        }

        if ($search = trim((string) $request->query('search', ''))) {
            $query->whereHas('student', fn ($students) => $students
                ->where('student_no', 'like', '%'.$search.'%')
                ->orWhere('first_name', 'like', '%'.$search.'%')
                ->orWhere('last_name', 'like', '%'.$search.'%'));
        }

        return view('enrollments.index', [
            'enrollments' => $query->latest('enrollment_datetime')->paginate(15)->withQueryString(),
            'replacementSections' => Section::query()->with('subject')->orderBy('section_code')->get(),
        ]);
    }

    public function create(): View
    {
        return view('enrollments.create', [
            'students' => Student::query()->where('status', 'Active')->orderBy('student_no')->get(),
            'sections' => Section::query()->with(['subject', 'instructor'])->withCount([
                'enrollments as enrolled_count' => fn ($query) => $query->where('status', 'Enrolled'),
            ])->orderBy('school_year')->orderBy('section_code')->get(),
        ]);
    }

    public function store(Request $request, EnrollmentService $service): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,student_id'],
            'section_id' => ['required', 'integer', 'exists:sections,section_id'],
        ]);

        $service->enroll((int) $data['student_id'], (int) $data['section_id'], (int) $request->user()->id);

        return redirect()->route('enrollments.index')->with('success', 'Student enrolled successfully.');
    }

    public function drop(Request $request, Enrollment $enrollment, EnrollmentService $service): RedirectResponse
    {
        $service->drop((int) $enrollment->getKey(), (int) $request->user()->id);

        return back()->with('success', 'Enrollment dropped successfully.');
    }

    public function dropAdd(Request $request, Enrollment $enrollment, EnrollmentService $service): RedirectResponse
    {
        $data = $request->validate([
            'section_id' => ['required', 'integer', Rule::exists('sections', 'section_id')],
        ]);

        $service->dropAndAdd((int) $enrollment->getKey(), (int) $data['section_id'], (int) $request->user()->id);

        return back()->with('success', 'Drop/add completed successfully.');
    }
}
