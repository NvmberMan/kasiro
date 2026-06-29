<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('images/kasiro-logo.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/kasiro-logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Wix+Madefor+Text:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
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
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <img src="{{ asset('images/kasiro-logo-black.png') }}" alt="Kasiro" class="h-5 w-auto">
                        <span class="text-sm font-semibold tracking-wide text-blue-500">STUDIO</span>
                    </a>
                </div>

                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" class="flex items-center focus:outline-none">
                        @if ($u->avatar)
                            <img src="{{ $u->avatar }}" alt="{{ $u->name }}" class="h-9 w-9 rounded-full object-cover ring-2 ring-slate-200 hover:ring-blue-400 transition">
                        @else
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 text-slate-500 ring-2 ring-slate-200 hover:ring-blue-400 transition">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                                </svg>
                            </span>
                        @endif
                    </button>

                    <div x-show="open" @click="open = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         style="display:none"
                         class="absolute end-0 mt-3 w-64 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 z-50 overflow-hidden">

                        {{-- User info --}}
                        <div class="flex items-center gap-3 px-4 py-4">
                            @if ($u->avatar)
                                <img src="{{ $u->avatar }}" alt="{{ $u->name }}" class="h-11 w-11 rounded-full object-cover shrink-0">
                            @else
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                                    </svg>
                                </span>
                            @endif
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $u->name }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ $u->email }}</p>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 mx-4"></div>

                        {{-- Menu items --}}
                        <div class="px-2 py-2">
                            <a href="{{ route('profile.edit') }}"
                               class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="font-medium">Profil</span>
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    <span class="font-medium">Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Sidebar --}}
            <aside class="fixed bottom-0 left-0 top-16 z-30 w-60 transform bg-[#0c2461] transition-transform duration-200 lg:translate-x-0 flex flex-col"
                   x-bind:class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
                <nav class="flex flex-col flex-1">
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
                <div class="p-4 border-t border-white/10">
                    <a href="{{ route('platform.home') }}"
                       class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium text-blue-100/70 hover:bg-white/5 hover:text-white transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Ke Landing Page
                    </a>
                </div>
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
        <x-flash-modal />
        @stack('scripts')
    </body>
</html>
