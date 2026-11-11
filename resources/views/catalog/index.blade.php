<x-layouts::app :title="$catalog['title']">
    <main class="mx-auto w-full max-w-7xl p-4 md:p-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">{{ $catalog['title'] }}</h1>
                <p class="mt-1 text-sm text-neutral-500">Manage {{ strtolower($catalog['title']) }} records.</p>
            </div>
            <a class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700" href="{{ route(request()->route('catalog').'.create') }}">Add {{ $catalog['singular'] }}</a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-50 p-3 text-green-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-50 p-3 text-red-800">{{ session('error') }}</div>
        @endif

        <form method="GET" class="mb-4 flex max-w-lg gap-2">
            <input class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900" name="search" value="{{ request('search') }}" placeholder="Search {{ strtolower($catalog['title']) }}">
            <button class="rounded-lg border px-4 py-2">Search</button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-50 dark:bg-neutral-800">
                    <tr>
                        @foreach ($catalog['columns'] as $label)
                            <th class="px-4 py-3 font-semibold">{{ $label }}</th>
                        @endforeach
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($records as $record)
                        <tr>
                            @foreach ($catalog['columns'] as $column => $label)
                                @php($value = data_get($record, $column))
                                <td class="px-4 py-3">
                                    @if ($column === 'method_available')
                                        {{ $value ? 'Available' : 'Unavailable' }}
                                    @else
                                        {{ $value ?? '—' }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="whitespace-nowrap px-4 py-3">
                                <a class="text-neutral-700 hover:underline" href="{{ route(request()->route('catalog').'.show', $record->getKey()) }}">View</a>
                                <a class="ml-3 text-blue-700 hover:underline" href="{{ route(request()->route('catalog').'.edit', $record->getKey()) }}">Edit</a>
                                <form class="ml-3 inline" method="POST" action="{{ route(request()->route('catalog').'.destroy', $record->getKey()) }}" onsubmit="return confirm('Delete this record?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-700 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-8 text-center text-neutral-500" colspan="{{ count($catalog['columns']) + 1 }}">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $records->links() }}</div>
    </main>
</x-layouts::app>
