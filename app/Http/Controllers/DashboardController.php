<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'studentCount' => Student::query()->count(),
            'activeStudentCount' => Student::query()->where('status', 'Active')->count(),
            'activeSectionCount' => Section::query()->count(),
            'activeEnrollmentCount' => Enrollment::query()->where('status', 'Enrolled')->count(),
            'paymentTotal' => Payment::query()->sum('amount'),
            'pendingAccountCount' => User::query()->where('status', 'Pending')->count(),
            'recentEnrollments' => Enrollment::query()->with(['student', 'section.subject'])
                ->latest('enrollment_datetime')->limit(8)->get(),
        ]);
    }
}
