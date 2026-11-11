<x-layouts::app :title="$catalog['singular'].' details'">
    <main class="mx-auto w-full max-w-3xl p-4 md:p-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold">{{ $catalog['singular'] }} details</h1>
            <a class="rounded-lg border px-4 py-2" href="{{ route(request()->route('catalog').'.edit', $record->getKey()) }}">Edit</a>
        </div>
        <dl class="divide-y divide-neutral-200 rounded-xl border border-neutral-200 dark:divide-neutral-700 dark:border-neutral-700">
            @foreach ($catalog['columns'] as $column => $label)
                @php($value = data_get($record, $column))
                <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                    <dt class="text-sm font-medium text-neutral-500">{{ $label }}</dt>
                    <dd class="sm:col-span-2">{{ $column === 'method_available' ? ($value ? 'Available' : 'Unavailable') : ($value ?? '—') }}</dd>
                </div>
            @endforeach
        </dl>
        <a class="mt-5 inline-block text-blue-700 hover:underline" href="{{ route(request()->route('catalog').'.index') }}">Back to {{ strtolower($catalog['title']) }}</a>
    </main>
</x-layouts::app>
