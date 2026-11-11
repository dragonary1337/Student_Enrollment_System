<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => 'Home'])
    </head>
    <body class="min-h-screen bg-neutral-50 text-neutral-900 dark:bg-neutral-950 dark:text-neutral-100">
        <header class="border-b border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-6 py-4">
                <a class="font-semibold" href="{{ route('home') }}">{{ config('app.name') }}</a>
                <nav class="flex items-center gap-3">
                    @auth
                        <a class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white" href="{{ route('dashboard') }}">Dashboard</a>
                    @else
                        <a class="rounded-lg px-4 py-2 text-sm font-medium hover:bg-neutral-100 dark:hover:bg-neutral-800" href="{{ route('login') }}">Log in</a>
                        <a class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" href="{{ route('register') }}">Register</a>
                    @endauth
                </nav>
            </div>
        </header>
        <main class="mx-auto max-w-6xl px-6 py-20">
            <p class="mb-3 text-sm font-semibold uppercase tracking-widest text-blue-700 dark:text-blue-400">University of Saint Freya</p>
            <h1 class="max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl">Student Enrollment System</h1>
            <p class="mt-5 max-w-2xl text-lg leading-8 text-neutral-600 dark:text-neutral-300">
                Manage student records, academic sections, enrollment changes, payments, and term reports in one secure system.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                @auth
                    <a class="rounded-lg bg-blue-600 px-5 py-3 font-medium text-white hover:bg-blue-700" href="{{ route('dashboard') }}">Open dashboard</a>
                @else
                    <a class="rounded-lg bg-blue-600 px-5 py-3 font-medium text-white hover:bg-blue-700" href="{{ route('login') }}">Sign in</a>
                    <a class="rounded-lg border border-neutral-300 px-5 py-3 font-medium hover:bg-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800" href="{{ route('register') }}">Create an account</a>
                @endauth
            </div>
            <section class="mt-16 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Student records', 'Maintain verified student profiles and program assignments.'],
                    ['Enrollment', 'Enroll, drop, or perform a same-term drop/add transaction.'],
                    ['Payments', 'Record term payments with an auditable processing history.'],
                    ['Reports', 'Filter student, enrollment, section, and payment reports.'],
                ] as [$title, $description])
                    <article class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-800 dark:bg-neutral-900">
                        <h2 class="font-semibold">{{ $title }}</h2>
                        <p class="mt-2 text-sm leading-6 text-neutral-600 dark:text-neutral-400">{{ $description }}</p>
                    </article>
                @endforeach
            </section>
            <p class="mt-8 text-sm text-neutral-500">New registrations remain pending until an administrator activates the account.</p>
        </main>
    </body>
</html>
