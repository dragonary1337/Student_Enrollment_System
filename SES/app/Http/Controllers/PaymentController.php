<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Student;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::query()->with(['student', 'paymentMethod', 'recorder']);

        if ($request->filled('school_year')) {
            $query->where('school_year', $request->query('school_year'));
        }

        if ($request->filled('semester') && in_array($request->query('semester'), ['1st', '2nd', 'Summer'], true)) {
            $query->where('semester', $request->query('semester'));
        }

        if ($search = trim((string) $request->query('search', ''))) {
            $query->whereHas('student', fn ($students) => $students->where('student_no', 'like', '%'.$search.'%')
                ->orWhere('first_name', 'like', '%'.$search.'%')
                ->orWhere('last_name', 'like', '%'.$search.'%'));
        }

        return view('payments.index', [
            'payments' => $query->latest('payment_datetime')->paginate(15)->withQueryString(),
            'total' => (clone $query)->sum('amount'),
        ]);
    }

    public function create(): View
    {
        return view('payments.create', [
            'students' => Student::query()->where('status', 'Active')->orderBy('student_no')->get(),
            'methods' => PaymentMethod::query()->where('method_available', true)->orderBy('method_name')->get(),
        ]);
    }

    public function store(Request $request, PaymentService $service): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,student_id'],
            'school_year' => ['required', 'string', 'size:9'],
            'semester' => ['required', Rule::in(['1st', '2nd', 'Summer'])],
            'amount' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,payment_method_id'],
        ]);

        $service->record($data, (int) $request->user()->id);

        return redirect()->route('payments.index')->with('success', 'Payment recorded successfully.');
    }
}
