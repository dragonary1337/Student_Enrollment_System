<x-layouts::app title="Enroll student">
    <main class="mx-auto w-full max-w-3xl p-4 md:p-8">
        <h1 class="mb-6 text-2xl font-semibold">Enroll student</h1>
        <form method="POST" action="{{ route('enrollments.store') }}" class="space-y-5 rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium" for="student_id">Active student</label>
                <select id="student_id" name="student_id" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900" required>
                    <option value="">Select a student</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->student_id }}" @selected(old('student_id') == $student->student_id)>{{ $student->student_no }} — {{ $student->full_name }}</option>
                    @endforeach
                </select>
                @error('student_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium" for="section_id">Section</label>
                <select id="section_id" name="section_id" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900" required>
                    <option value="">Select a section</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->section_id }}" @selected(old('section_id') == $section->section_id)>
                            {{ $section->section_code }} — {{ $section->subject?->subject_code }} ({{ $section->school_year }}, {{ $section->semester }}) · {{ $section->enrolled_count }}/{{ $section->max_capacity }}
                        </option>
                    @endforeach
                </select>
                @error('section_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3">
                <button class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">Enroll</button>
                <a class="rounded-lg border px-4 py-2" href="{{ route('enrollments.index') }}">Cancel</a>
            </div>
        </form>
    </main>
</x-layouts::app>
