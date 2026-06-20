<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Toko — Custom') }}
        </h2>
    </x-slot>

    @php
        $currentLayout  = old('layout', $defaults['layout']);
        $currentTheme   = old('theme', $defaults['theme']);
        $currentPalette = old('color_palette', $defaults['color_palette']);

        // Map theme -> its allowed palettes (for Alpine) and ensure current palette is valid for current theme
        $validPalettes = collect($themes)->map(fn ($t) => $t['palettes']);
        if (! in_array($currentPalette, $themes[$currentTheme]['palettes'] ?? [], true)) {
            $currentPalette = $themes[$currentTheme]['palettes'][0] ?? $defaults['color_palette'];
        }
    @endphp

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('tenants.store.custom') }}" enctype="multipart/form-data"
                      x-data="{
                          activeTheme: @js($currentTheme),
                          activePalette: @js($currentPalette),
                          themePalettes: @js($validPalettes),
                          changeTheme(t) {
                              this.activeTheme = t;
                              const ps = this.themePalettes[t] ?? [];
                              if (!ps.includes(this.activePalette)) this.activePalette = ps[0] ?? '';
                          }
                      }">
                    @csrf

                    {{-- Branding fields --}}
                    @include('tenants.partials.branding-fields')

                    {{-- Layout --}}
                    <div class="mt-6">
                        <x-input-label :value="__('Layout')" />
                        <p class="text-xs text-gray-400 mb-2">Posisi navigasi dan susunan halaman.</p>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach ($layouts as $key => $layout)
                                <label class="cursor-pointer relative block">
                                    <input type="radio" name="layout" value="{{ $key }}" class="sr-only peer"
                                        @checked($currentLayout === $key)>
                                    <div class="border-2 rounded-lg p-3 transition
                                                peer-checked:border-indigo-500 peer-checked:bg-indigo-50
                                                border-gray-200 hover:border-gray-300">
                                        <x-layout-wireframe :type="$key" />
                                        <p class="mt-2 text-center text-sm font-medium text-gray-700">{{ $layout['label'] }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('layout')" class="mt-2" />
                    </div>

                    {{-- Theme --}}
                    <div class="mt-6">
                        <x-input-label :value="__('Tema')" />
                        <p class="text-xs text-gray-400 mb-2">Gaya tipografi dan karakter visual toko.</p>
                        <input type="hidden" name="theme" :value="activeTheme">
                        <div class="grid grid-cols-3 gap-3">
                            @foreach ($themes as $key => $theme)
                                <div class="cursor-pointer border-2 rounded-lg p-3 transition"
                                     :class="activeTheme === '{{ $key }}' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'"
                                     @click="changeTheme('{{ $key }}')">
                                    <p class="text-lg font-semibold text-gray-800 mb-1 leading-none"
                                       style="font-family: {{ $theme['vars']['--brand-font'] }}">
                                        Aa
                                    </p>
                                    <p class="text-xs font-medium text-gray-600">{{ $theme['label'] }}</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ count($theme['palettes']) }} palet</p>
                                </div>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('theme')" class="mt-2" />
                    </div>

                    {{-- Color palette (per theme) --}}
                    <div class="mt-6">
                        <x-input-label :value="__('Palet Warna')" />
                        <p class="text-xs text-gray-400 mb-2">Pilihan warna berdasarkan tema yang dipilih.</p>
                        <input type="hidden" name="color_palette" :value="activePalette">
                        @foreach ($themes as $themeKey => $theme)
                            <div x-show="activeTheme === '{{ $themeKey }}'" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach ($theme['palettes'] as $paletteKey)
                                    @php $p = $palettes[$paletteKey] @endphp
                                    <div class="cursor-pointer border-2 rounded-lg p-3 transition"
                                         :class="activePalette === '{{ $paletteKey }}' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'"
                                         @click="activePalette = '{{ $paletteKey }}'">
                                        <div class="flex gap-1.5 mb-2">
                                            <span class="h-5 w-5 rounded-full" style="background:{{ $p['--brand-primary'] }}"></span>
                                            <span class="h-5 w-5 rounded-full" style="background:{{ $p['--brand-accent'] }}"></span>
                                            <span class="h-5 w-5 rounded-full border" style="background:{{ $p['--brand-bg'] }}"></span>
                                        </div>
                                        <p class="text-xs font-medium text-gray-600">{{ ucfirst($paletteKey) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        <x-input-error :messages="$errors->get('color_palette')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-4">
                        <a href="{{ route('tenants.choose') }}" class="text-sm text-gray-600 hover:text-gray-900">
                            {{ __('Kembali') }}
                        </a>
                        <x-primary-button>{{ __('Buat Toko') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
