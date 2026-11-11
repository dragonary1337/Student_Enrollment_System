<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('SES')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('students.index')" :current="request()->routeIs('students.*')" wire:navigate>
                        {{ __('Students') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('enrollments.index')" :current="request()->routeIs('enrollments.*')" wire:navigate>
                        {{ __('Enrollments') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="credit-card" :href="route('payments.index')" :current="request()->routeIs('payments.*')" wire:navigate>
                        {{ __('Payments') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chart-bar" :href="route('reports.index')" :current="request()->routeIs('reports.*')" wire:navigate>
                        {{ __('Reports') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @if (auth()->user()->isAdmin())
                    <flux:sidebar.group :heading="__('Academic setup')" class="grid">
                        <flux:sidebar.item icon="building-library" :href="route('departments.index')" :current="request()->routeIs('departments.*')" wire:navigate>{{ __('Departments') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="rectangle-stack" :href="route('programs.index')" :current="request()->routeIs('programs.*')" wire:navigate>{{ __('Programs') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="book-open" :href="route('subjects.index')" :current="request()->routeIs('subjects.*')" wire:navigate>{{ __('Subjects') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="academic-cap" :href="route('instructors.index')" :current="request()->routeIs('instructors.*')" wire:navigate>{{ __('Instructors') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="squares-2x2" :href="route('sections.index')" :current="request()->routeIs('sections.*')" wire:navigate>{{ __('Sections') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="banknotes" :href="route('payment-methods.index')" :current="request()->routeIs('payment-methods.*')" wire:navigate>{{ __('Payment methods') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="user-group" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>{{ __('User accounts') }}</flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
