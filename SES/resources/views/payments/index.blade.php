<x-layouts::app title="Payments">
    <main class="mx-auto w-full max-w-7xl p-4 md:p-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <div><h1 class="text-2xl font-semibold">Payments</h1><p class="mt-1 text-sm text-neutral-500">Recorded payments are immutable audit records.</p></div>
            <a class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white" href="{{ route('payments.create') }}">Record payment</a>
        </div>
        @if (session('success'))<div class="mb-4 rounded-lg bg-green-50 p-3 text-green-800">{{ session('success') }}</div>@endif
        <form class="mb-4 flex flex-wrap gap-2" method="GET">
            <input name="search" value="{{ request('search') }}" class="rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900" placeholder="Student number or name">
            <input name="school_year" value="{{ request('school_year') }}" class="rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900" placeholder="School year">
            <select name="semester" class="rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900">
                <option value="">All semesters</option>
                @foreach (['1st', '2nd', 'Summer'] as $semester)<option value="{{ $semester }}" @selected(request('semester') === $semester)>{{ $semester }}</option>@endforeach
            </select>
            <button class="rounded-lg border px-4 py-2">Filter</button>
        </form>
        <p class="mb-3 text-sm font-semibold">Filtered total: {{ number_format((float) $total, 2) }}</p>
        <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-50 dark:bg-neutral-800"><tr><th class="px-4 py-3">Student</th><th class="px-4 py-3">Term</th><th class="px-4 py-3">Amount</th><th class="px-4 py-3">Method</th><th class="px-4 py-3">Recorded</th><th class="px-4 py-3">By</th></tr></thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($payments as $payment)
                        <tr><td class="px-4 py-3">{{ $payment->student?->student_no }} · {{ $payment->student?->full_name }}</td><td class="px-4 py-3">{{ $payment->school_year }} · {{ $payment->semester }}</td><td class="px-4 py-3">{{ number_format((float) $payment->amount, 2) }}</td><td class="px-4 py-3">{{ $payment->paymentMethod?->method_name }}</td><td class="px-4 py-3">{{ $payment->payment_datetime?->format('Y-m-d H:i') }}</td><td class="px-4 py-3">{{ $payment->recorder?->name ?? '—' }}</td></tr>
                    @empty
                        <tr><td class="px-4 py-8 text-center text-neutral-500" colspan="6">No payments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $payments->links() }}</div>
    </main>
</x-layouts::app>
