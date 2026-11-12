<x-layouts::app title="Enrollments">
    <main class="mx-auto w-full max-w-7xl p-4 md:p-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <div><h1 class="text-2xl font-semibold">Enrollments</h1><p class="mt-1 text-sm text-neutral-500">Register students in sections and maintain enrollment statuses.</p></div>
            <a class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white" href="{{ route('enrollments.create') }}">Enroll student</a>
        </div>
        @if (session('success'))<div class="mb-4 rounded-lg bg-green-50 p-3 text-green-800">{{ session('success') }}</div>@endif
        @if ($errors->any())<div class="mb-4 rounded-lg bg-red-50 p-3 text-red-800">{{ $errors->first() }}</div>@endif
        <form class="mb-4 flex flex-wrap gap-2" method="GET">
            <input name="search" value="{{ request('search') }}" class="rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900" placeholder="Student number or name">
            <select name="status" class="rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900">
                <option value="">All statuses</option>
                @foreach (['Enrolled', 'Dropped', 'Completed'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach
            </select>
            <button class="rounded-lg border px-4 py-2">Filter</button>
        </form>
        <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-50 dark:bg-neutral-800"><tr><th class="px-4 py-3">Student</th><th class="px-4 py-3">Section / subject</th><th class="px-4 py-3">Term</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Actions</th></tr></thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($enrollments as $enrollment)
                        <tr>
                            <td class="px-4 py-3">{{ $enrollment->student?->student_no }} · {{ $enrollment->student?->full_name }}</td>
                            <td class="px-4 py-3">{{ $enrollment->section?->section_code }} · {{ $enrollment->section?->subject?->subject_code }}</td>
                            <td class="px-4 py-3">{{ $enrollment->section?->school_year }} · {{ $enrollment->section?->semester }}</td>
                            <td class="px-4 py-3">{{ $enrollment->enrollment_datetime?->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">{{ $enrollment->status }}</td>
                            <td class="px-4 py-3">
                                @if ($enrollment->status === 'Enrolled')
                                    <form method="POST" action="{{ route('enrollments.drop', $enrollment) }}" onsubmit="return confirm('Drop this enrollment?')">
                                        @csrf
                                        <button class="text-red-700 hover:underline">Drop</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-8 text-center text-neutral-500" colspan="6">No enrollments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $enrollments->links() }}</div>
    </main>
</x-layouts::app>
