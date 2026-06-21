<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Wix+Madefor+Text:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            [x-cloak]{display:none !important}
            .ph{
                background-color:#f3f4f6;
                background-image:
                    linear-gradient(45deg,#e5e7eb 25%,transparent 25%),
                    linear-gradient(-45deg,#e5e7eb 25%,transparent 25%),
                    linear-gradient(45deg,transparent 75%,#e5e7eb 75%),
                    linear-gradient(-45deg,transparent 75%,#e5e7eb 75%);
                background-size:22px 22px;
                background-position:0 0,0 11px,11px -11px,-11px 0;
            }
        </style>
    </head>
    <body class="font-sans antialiased text-slate-900">
        @php
            $u = auth()->user();
            $nav = [
                ['route' => 'dashboard',  'label' => 'Beranda',    'icon' => 'M3 11.5L12 4l9 7.5M5 10v9a1 1 0 001 1h12a1 1 0 001-1v-9'],
                ['route' => 'my-stores',  'label' => 'Kasir Saya',  'icon' => 'M4 5h6v6H4zM14 5h6v6h-6zM4 15h6v4H4zM14 13h6v6h-6z'],
                ['route' => 'archive',    'label' => 'Arsip',       'icon' => 'M3 7h18M5 7v12a1 1 0 001 1h12a1 1 0 001-1V7M9 11h6'],
            ];
        @endphp

        <div x-data="{ sidebar: false }" class="min-h-screen bg-gray-100">
            {{-- Topbar --}}
            <header class="fixed inset-x-0 top-0 z-40 flex h-16 items-center justify-between border-b border-slate-100 bg-white px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button type="button" class="lg:hidden text-slate-500" x-on:click="sidebar = ! sidebar" aria-label="Menu">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('images/kasiro-logo-black.png') }}" alt="Kasiro" class="h-5 w-auto">
                    </a>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center focus:outline-none">
                            @if ($u->avatar)
                                <img src="{{ $u->avatar }}" alt="{{ $u->name }}" class="h-9 w-9 rounded-full object-cover ring-1 ring-slate-200">
                            @else
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 text-sm font-semibold text-slate-600">
                                    {{ strtoupper(mb_substr($u->name, 0, 1)) }}
                                </span>
                            @endif
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <div class="text-sm font-medium text-slate-800">{{ $u->name }}</div>
                            <div class="truncate text-xs text-slate-500">{{ $u->email }}</div>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profil') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Keluar') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </header>

            {{-- Sidebar --}}
            <aside class="fixed bottom-0 left-0 top-16 z-30 w-60 transform bg-[#0c2461] transition-transform duration-200 lg:translate-x-0"
                   x-bind:class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
                <nav class="flex flex-col py-4">
                    @foreach ($nav as $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-6 py-3.5 text-sm font-medium transition
                                  {{ $active ? 'bg-blue-700 text-white' : 'text-blue-100/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                            {{ __($item['label']) }}
                        </a>
                    @endforeach
                </nav>
            </aside>

            {{-- Overlay (mobile) --}}
            <div x-show="sidebar" x-cloak x-on:click="sidebar = false"
                 class="fixed inset-0 z-20 bg-black/30 lg:hidden"></div>

            {{-- Main --}}
            <main class="min-h-screen pt-16 lg:pl-60">
                @isset($header)
                    <div class="px-6 pt-6 sm:px-8">{{ $header }}</div>
                @endisset
                <div class="p-6 sm:p-8">
                    {{ $slot }}
                </div>
            </main>
        </div>

        @include('partials.confirm-modal')
    </body>
</html>
