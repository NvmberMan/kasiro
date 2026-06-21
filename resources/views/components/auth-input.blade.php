@props([
    'name',
    'type' => 'text',
    'icon' => null,
    'value' => '',
    'autocomplete' => null,
])

@php
    $icons = [
        'mail' => 'M3 8l9 6 9-6M3 8v8a2 2 0 002 2h14a2 2 0 002-2V8M3 8a2 2 0 012-2h14a2 2 0 012 2',
        'lock' => 'M16 11V7a4 4 0 10-8 0v4M5 11h14a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1v-7a1 1 0 011-1z',
        'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    ];
    $iconPath = $icon ? ($icons[$icon] ?? null) : null;
    $isPassword = $type === 'password';
@endphp

<div @if ($isPassword) x-data="{ show: false }" @endif>
    <div class="relative">
        @if ($iconPath)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
                </svg>
            </span>
        @endif

        <input
            name="{{ $name }}"
            id="{{ $name }}"
            @if ($isPassword) x-bind:type="show ? 'text' : 'password'" @else type="{{ $type }}" @endif
            value="{{ $value }}"
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            {{ $attributes->merge(['class' => 'w-full rounded-full border border-gray-200 bg-gray-50 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition focus:border-lime-500 focus:bg-white focus:ring-lime-500 '.($iconPath ? 'pl-11' : 'pl-4').' '.($isPassword ? 'pr-11' : 'pr-4')]) }}
        >

        @if ($isPassword)
            <button type="button" x-on:click="show = !show"
                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-600 focus:outline-none"
                    :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'">
                <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S5.5 5.5 12 5.5 21.5 12 21.5 12 18.5 18.5 12 18.5 2.5 12 2.5 12z" />
                </svg>
                <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a3 3 0 004.2 4.2M9.9 4.9A9.5 9.5 0 0112 4.5c6.5 0 9.5 7.5 9.5 7.5a14 14 0 01-2.3 3.3M6.3 6.3A14 14 0 002.5 12s3 6.5 9.5 6.5a9 9 0 003.3-.6" />
                </svg>
            </button>
        @endif
    </div>

    <x-input-error :messages="$errors->get($name)" class="mt-1 ml-3" />
</div>
