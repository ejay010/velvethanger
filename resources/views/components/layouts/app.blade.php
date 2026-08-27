<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
    {{-- Main Storefront Header & Navigation --}}
    <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <x-app-logo href="{{ route('home') }}" wire:navigate />

        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item icon="home" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>
                {{ __('Home') }}
            </flux:navbar.item>
            <flux:navbar.item :href="route('cart')" :current="request()->routeIs('cart')" wire:navigate>
                <livewire:storefront.cart-badge />
            </flux:navbar.item>
        </flux:navbar>

        <flux:spacer />

        <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
            @auth
                <x-desktop-user-menu />
            @else
                <flux:navbar.item icon="arrow-right-start-on-rectangle" :href="route('login')" wire:navigate>
                    {{ __('Log in') }}
                </flux:navbar.item>
            @endauth
        </flux:navbar>
    </flux:header>

    <main class="py-6">
        {{ $slot }}
    </main>

    @fluxScripts
</body>
</html>

