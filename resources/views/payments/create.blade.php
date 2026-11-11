<x-layouts::app title="Record payment">
    <main class="mx-auto w-full max-w-3xl p-4 md:p-8">
        <h1 class="mb-6 text-2xl font-semibold">Record payment</h1>
        <form method="POST" action="{{ route('payments.store') }}" class="space-y-5 rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium" for="student_id">Student</label>
                <select id="student_id" name="student_id" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900" required>
                    <option value="">Select a student</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->student_id }}" @selected(old('student_id') == $student->student_id)>{{ $student->student_no }} — {{ $student->full_name }}</option>
                    @endforeach
                </select>
                @error('student_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-1 block text-sm font-medium" for="school_year">School year</label><input id="school_year" name="school_year" value="{{ old('school_year') }}" placeholder="2025-2026" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900" required>@error('school_year')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                <div>
                    <label class="mb-1 block text-sm font-medium" for="semester">Semester</label>
                    <select id="semester" name="semester" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900" required>
                        <option value="">Select semester</option>
                        @foreach (['1st', '2nd', 'Summer'] as $semester)<option value="{{ $semester }}" @selected(old('semester') === $semester)>{{ $semester }}</option>@endforeach
                    </select>
                    @error('semester')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div><label class="mb-1 block text-sm font-medium" for="amount">Amount</label><input id="amount" name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount') }}" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900" required>@error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                <div>
                    <label class="mb-1 block text-sm font-medium" for="payment_method_id">Payment method</label>
                    <select id="payment_method_id" name="payment_method_id" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900" required>
                        <option value="">Select method</option>
                        @foreach ($methods as $method)<option value="{{ $method->payment_method_id }}" @selected(old('payment_method_id') == $method->payment_method_id)>{{ $method->method_name }}</option>@endforeach
                    </select>
                    @error('payment_method_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex gap-3"><button class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white">Save payment</button><a class="rounded-lg border px-4 py-2" href="{{ route('payments.index') }}">Cancel</a></div>
        </form>
    </main>
</x-layouts::app>
