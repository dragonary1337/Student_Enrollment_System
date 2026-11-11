<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navbar.item>
                <flux:navbar.item icon="users" :href="route('students.index')" :current="request()->routeIs('students.*')" wire:navigate>{{ __('Students') }}</flux:navbar.item>
                <flux:navbar.item icon="clipboard-document-list" :href="route('enrollments.index')" :current="request()->routeIs('enrollments.*')" wire:navigate>{{ __('Enrollments') }}</flux:navbar.item>
                <flux:navbar.item icon="credit-card" :href="route('payments.index')" :current="request()->routeIs('payments.*')" wire:navigate>{{ __('Payments') }}</flux:navbar.item>
                <flux:navbar.item icon="chart-bar" :href="route('reports.index')" :current="request()->routeIs('reports.*')" wire:navigate>{{ __('Reports') }}</flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <x-desktop-user-menu />
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('SES')">
                    <flux:sidebar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard')  }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('students.index')" :current="request()->routeIs('students.*')" wire:navigate>{{ __('Students') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('enrollments.index')" :current="request()->routeIs('enrollments.*')" wire:navigate>{{ __('Enrollments') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="credit-card" :href="route('payments.index')" :current="request()->routeIs('payments.*')" wire:navigate>{{ __('Payments') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="chart-bar" :href="route('reports.index')" :current="request()->routeIs('reports.*')" wire:navigate>{{ __('Reports') }}</flux:sidebar.item>
                    @if (auth()->user()->isAdmin())
                        <flux:sidebar.item icon="user-group" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>{{ __('User accounts') }}</flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

        </flux:sidebar>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
