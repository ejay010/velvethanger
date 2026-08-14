<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
    <flux:sidebar sticky collapsible="mobile"
        class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" />

            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.item icon="squares-2x2" href="{{ route('admin.dashboard') }}"
                :current="request()->routeIs('admin.dashboard')">Dashboard
            </flux:sidebar.item>
            <flux:sidebar.item icon="rectangle-stack" badge="12" href="{{ route('admin.products') }}"
                :current="request()->routeIs('admin.products')">Products
            </flux:sidebar.item>
            <flux:sidebar.item icon="document-currency-dollar" href="{{ route('admin.orders') }}"
                :current="request()->routeIs('admin.orders')">Orders
            </flux:sidebar.item>
            <flux:sidebar.item icon="chart-bar" href="#">Reports</flux:sidebar.item>
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        <flux:sidebar.nav>
            <flux:sidebar.item icon="cog-6-tooth" href="#">Settings</flux:sidebar.item>
            <flux:sidebar.item icon="information-circle" href="#">Help</flux:sidebar.item>
        </flux:sidebar.nav>

        <flux:dropdown position="top" align="start" class="max-lg:hidden">
            <flux:sidebar.profile avatar="https://fluxui.dev/img/demo/user.png" name="Olivia Martin" />

            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.radio checked>Olivia Martin</flux:menu.radio>
                    <flux:menu.radio>Truly Delta</flux:menu.radio>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.item icon="arrow-right-start-on-rectangle">Logout</flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" alignt="start">
            <flux:profile avatar="https://fluxui.dev/img/demo/user.png" />

            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.radio checked>Olivia Martin</flux:menu.radio>
                    <flux:menu.radio>Truly Delta</flux:menu.radio>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.item icon="arrow-right-start-on-rectangle">Logout</flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    @if (request()->routeIs('admin.products') || request()->routeIs('admin.category'))
        <flux:header
            class="block! bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
            <flux:navbar scrollable>
                <flux:navbar.item icon="" href="{{ route('admin.products') }}"
                    :current="request()->routeIs('admin.products')">Products
                </flux:navbar.item>
                <flux:navbar.item icon="" href="{{ route('admin.category') }}"
                    :current="request()->routeIs('admin.category')">
                    Categories
                </flux:navbar.item>
            </flux:navbar>
        </flux:header>
    @endif

    <flux:main>
        {{ $slot }}
    </flux:main>

    @fluxScripts
</body>

</html>
