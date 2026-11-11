<x-layouts::app title="User accounts">
    <main class="mx-auto w-full max-w-7xl p-4 md:p-8">
        <div class="mb-6"><h1 class="text-2xl font-semibold">User account approvals</h1><p class="mt-1 text-sm text-neutral-500">Activate registrations and assign Operator or Admin access.</p></div>
        @if (session('success'))<div class="mb-4 rounded-lg bg-green-50 p-3 text-green-800">{{ session('success') }}</div>@endif
        @if ($errors->any())<div class="mb-4 rounded-lg bg-red-50 p-3 text-red-800">{{ $errors->first() }}</div>@endif
        <form class="mb-4" method="GET">
            <select name="status" class="rounded-lg border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900">
                <option value="">All statuses</option>
                @foreach (['Pending', 'Active', 'Inactive'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach
            </select>
            <button class="rounded-lg border px-4 py-2">Filter</button>
        </form>
        <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-50 dark:bg-neutral-800"><tr><th class="px-4 py-3">Account</th><th class="px-4 py-3">Registered</th><th class="px-4 py-3">Last login</th><th class="px-4 py-3">Access</th></tr></thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-4 py-3"><strong>{{ $user->name }}</strong><br><span class="text-neutral-500">{{ $user->username }} · {{ $user->email }}</span></td>
                            <td class="px-4 py-3">{{ $user->created_at?->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">{{ $user->last_login?->format('Y-m-d H:i') ?? 'Never' }}</td>
                            <td class="px-4 py-3">
                                <form class="flex flex-wrap items-center gap-2" method="POST" action="{{ route('users.update', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" class="rounded border-neutral-300 text-sm dark:border-neutral-700 dark:bg-neutral-900">
                                        @foreach (['Operator', 'Admin'] as $role)<option value="{{ $role }}" @selected($user->role === $role)>{{ $role }}</option>@endforeach
                                    </select>
                                    <select name="status" class="rounded border-neutral-300 text-sm dark:border-neutral-700 dark:bg-neutral-900">
                                        @foreach (['Pending', 'Active', 'Inactive'] as $status)<option value="{{ $status }}" @selected($user->status === $status)>{{ $status }}</option>@endforeach
                                    </select>
                                    <button class="rounded border px-3 py-1 hover:bg-neutral-100 dark:hover:bg-neutral-800">Save</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-8 text-center text-neutral-500" colspan="4">No accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $users->links() }}</div>
    </main>
</x-layouts::app>
