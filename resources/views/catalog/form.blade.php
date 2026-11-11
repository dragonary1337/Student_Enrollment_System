<x-layouts::app :title="($record ? 'Edit ' : 'Add ').$catalog['singular']">
    <main class="mx-auto w-full max-w-3xl p-4 md:p-8">
        <h1 class="mb-6 text-2xl font-semibold">{{ $record ? 'Edit ' : 'Add ' }}{{ $catalog['singular'] }}</h1>
        <form method="POST" action="{{ $record ? route(request()->route('catalog').'.update', $record->getKey()) : route(request()->route('catalog').'.store') }}" class="space-y-5 rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            @csrf
            @if ($record)
                @method('PUT')
            @endif
            @foreach ($catalog['fields'] as $name => $field)
                @php($value = old($name, $record?->{$name}))
                <div>
                    <label class="mb-1 block text-sm font-medium" for="{{ $name }}">{{ $field['label'] }}</label>
                    @if (isset($field['options']))
                        <select id="{{ $name }}" name="{{ $name }}" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900" @required(in_array('required', $field['rules'], true))>
                            <option value="">Select {{ strtolower($field['label']) }}</option>
                            @foreach ($field['options'] as $optionValue => $optionLabel)
                                <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    @else
                        <input id="{{ $name }}" name="{{ $name }}" type="{{ $field['type'] ?? 'text' }}" value="{{ $value }}" @if (isset($field['step'])) step="{{ $field['step'] }}" @endif class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900" @required(in_array('required', $field['rules'], true))>
                    @endif
                    @error($name)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            @endforeach
            <div class="flex gap-3">
                <button class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">Save</button>
                <a class="rounded-lg border px-4 py-2" href="{{ route(request()->route('catalog').'.index') }}">Cancel</a>
            </div>
        </form>
    </main>
</x-layouts::app>
