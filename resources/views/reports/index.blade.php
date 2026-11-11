<x-layouts::app title="System reports">
    <main class="mx-auto w-full max-w-7xl p-4 md:p-8">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div><h1 class="text-2xl font-semibold">System reports</h1><p class="mt-1 text-sm text-neutral-500">Filter current records, export a spreadsheet-compatible CSV, or print to PDF.</p></div>
            <div class="flex gap-2 print:hidden">
                <a class="rounded-lg border px-4 py-2" href="{{ route('reports.export', request()->query()) }}">Export CSV</a>
                <button class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white" onclick="window.print()">Print / Save PDF</button>
            </div>
        </div>
        <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 print:hidden">
            <div><label class="mb-1 block text-sm" for="type">Report</label><select id="type" name="type" class="rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900">
                @foreach (['students' => 'Students', 'enrollments' => 'Enrollments', 'sections' => 'Section availability', 'payments' => 'Payments'] as $key => $label)<option value="{{ $key }}" @selected($filters['type'] === $key)>{{ $label }}</option>@endforeach
            </select></div>
            <div><label class="mb-1 block text-sm" for="status">Status</label><input id="status" name="status" value="{{ $filters['status'] ?? '' }}" class="rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900" placeholder="Optional"></div>
            <div><label class="mb-1 block text-sm" for="school_year">School year</label><input id="school_year" name="school_year" value="{{ $filters['school_year'] ?? '' }}" class="rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900" placeholder="2025-2026"></div>
            <div><label class="mb-1 block text-sm" for="semester">Semester</label><select id="semester" name="semester" class="rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900">
                <option value="">All semesters</option>
                @foreach (['1st', '2nd', 'Summer'] as $semester)<option value="{{ $semester }}" @selected(($filters['semester'] ?? '') === $semester)>{{ $semester }}</option>@endforeach
            </select></div>
            <button class="rounded-lg bg-neutral-800 px-4 py-2 text-white">Apply filters</button>
        </form>
        <section>
            <h2 class="mb-3 text-xl font-semibold">{{ $title }}</h2>
            <p class="mb-3 text-sm text-neutral-500">{{ count($rows) }} records</p>
            <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
                <table class="w-full text-left text-sm">
                    <thead class="bg-neutral-50 dark:bg-neutral-800"><tr>@foreach ($headers as $header)<th class="whitespace-nowrap px-4 py-3">{{ $header }}</th>@endforeach</tr></thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse ($rows as $row)
                            <tr>@foreach ($row as $value)<td class="whitespace-nowrap px-4 py-3">{{ $value === '' ? '—' : $value }}</td>@endforeach</tr>
                        @empty
                            <tr><td class="px-4 py-8 text-center text-neutral-500" colspan="{{ count($headers) }}">No records match the selected filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <style>
        @media print { aside, header, nav, form, .print\:hidden { display: none !important; } body { background: white !important; } }
    </style>
</x-layouts::app>
