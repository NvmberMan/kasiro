@props(['align' => 'right'])

@php
    $locales = \App\Support\Locale::supported();
    $current = app()->getLocale();
    $menuAlign = $align === 'left' ? 'start-0 origin-top-left' : 'end-0 origin-top-right';
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button type="button" @click="open = ! open"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-100 focus:outline-none transition"
            :aria-expanded="open" aria-haspopup="true"
            aria-label="{{ __('Ganti bahasa') }}">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zM3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18M12 3a15 15 0 000 18" />
        </svg>
        <span class="hidden sm:inline">{{ strtoupper($current) }}</span>
        <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-50 mt-2 w-44 rounded-md shadow-lg bg-white ring-1 ring-black/5 py-1 {{ $menuAlign }}"
         style="display: none;">
        @foreach ($locales as $code => $label)
            <a href="{{ route('locale.update', $code) }}"
               class="flex items-center justify-between px-4 py-2 text-sm {{ $code === $current ? 'font-semibold text-indigo-600' : 'text-gray-700 hover:bg-gray-100' }}">
                <span>{{ $label }}</span>
                @if ($code === $current)
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                @endif
            </a>
        @endforeach
    </div>
</div>
