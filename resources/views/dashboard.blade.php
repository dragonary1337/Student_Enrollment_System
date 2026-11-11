<x-layouts::app title="SES Dashboard">
    <main class="mx-auto w-full max-w-7xl p-4 md:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold">Student Enrollment System</h1>
            <p class="mt-1 text-sm text-neutral-500">Welcome, {{ auth()->user()->first_name ?: auth()->user()->name }}.</p>
        </div>
        @if (session('success'))<div class="mb-4 rounded-lg bg-green-50 p-3 text-green-800">{{ session('success') }}</div>@endif
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['Registered students', $studentCount],
                ['Active students', $activeStudentCount],
                ['Sections', $activeSectionCount],
                ['Current enrollments', $activeEnrollmentCount],
                ['Recorded payments', number_format((float) $paymentTotal, 2)],
                ['Pending accounts', $pendingAccountCount],
            ] as [$label, $value])
                <section class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
                    <p class="text-sm text-neutral-500">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-semibold">{{ $value }}</p>
                </section>
            @endforeach
        </div>
        <section class="mt-8">
            <h2 class="mb-3 text-lg font-semibold">Recent enrollment activity</h2>
            <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
                <table class="w-full text-left text-sm">
                    <thead class="bg-neutral-50 dark:bg-neutral-800"><tr><th class="px-4 py-3">Student</th><th class="px-4 py-3">Subject / section</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Status</th></tr></thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse ($recentEnrollments as $enrollment)
                            <tr><td class="px-4 py-3">{{ $enrollment->student?->student_no }} · {{ $enrollment->student?->full_name }}</td><td class="px-4 py-3">{{ $enrollment->section?->subject?->subject_code }} · {{ $enrollment->section?->section_code }}</td><td class="px-4 py-3">{{ $enrollment->enrollment_datetime?->format('Y-m-d H:i') }}</td><td class="px-4 py-3">{{ $enrollment->status }}</td></tr>
                        @empty
                            <tr><td class="px-4 py-8 text-center text-neutral-500" colspan="4">No enrollment activity yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-layouts::app>
