<x-app-layout>
    @php
        $currentLayout  = old('layout', $defaults['layout']);
        $currentTheme   = old('theme', $defaults['theme']);
        $currentPalette = old('color_palette', $defaults['color_palette']);

        $validPalettes = collect($themes)->map(fn ($t) => $t['palettes']);
        if (! in_array($currentPalette, $themes[$currentTheme]['palettes'] ?? [], true)) {
            $currentPalette = $themes[$currentTheme]['palettes'][0] ?? $defaults['color_palette'];
        }
    @endphp

    <div x-data="{
            loading: false,
            activeLayout: @js($currentLayout),
            activeTheme: @js($currentTheme),
            activePalette: @js($currentPalette),
            themePalettes: @js($validPalettes),
            logoPreview: null,
            changeTheme(t) {
                this.activeTheme = t;
                const ps = this.themePalettes[t] ?? [];
                if (!ps.includes(this.activePalette)) this.activePalette = ps[0] ?? '';
            },
            handleLogo(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = ev => this.logoPreview = ev.target.result;
                    reader.readAsDataURL(file);
                }
            }
        }" class="py-10">

        {{-- Loading overlay --}}
        <div x-show="loading"
             style="display:none"
             class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-[#f3f4f3]">
            <img src="{{ asset('images/kasiro-logo-black.png') }}" alt="Kasiro" class="mb-10 h-12 w-auto">
            <div class="relative h-7 w-80 overflow-hidden rounded-full bg-[#e8efb0]">
                <div class="progress-ball absolute top-1/2 -translate-y-1/2 h-7 w-7 rounded-full bg-[#1e3a8a] shadow-md"></div>
            </div>
            <p class="mt-4 text-sm text-slate-500">Loading...</p>
        </div>

        <form method="POST" action="{{ route('tenants.store.custom') }}"
              enctype="multipart/form-data"
              @submit="loading = true">
            @csrf

            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">

                    {{-- Left: Rincian Kasir --}}
                    <div>
                        <h2 class="mb-4 text-2xl font-bold tracking-tight text-slate-900">Rincian Kasir</h2>
                        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">

                            {{-- Nama Toko --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-800">Nama Toko</label>
                                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                                       placeholder="Contoh: Warung Budi"
                                       class="w-full rounded-full border border-slate-300 px-5 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                @error('name')
                                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Nama Domain --}}
                            <div class="mt-5">
                                <label class="mb-1.5 block text-sm font-semibold text-slate-800">Nama Domain</label>
                                <div class="flex items-center">
                                    <input type="text" name="subdomain" value="{{ old('subdomain') }}" required
                                           placeholder="namatoko"
                                           class="flex-1 rounded-l-full border border-r-0 border-slate-300 px-5 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:z-10">
                                    <span class="flex items-center rounded-r-full border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-500 whitespace-nowrap">
                                        .{{ config('tenancy.central_domain') }}
                                    </span>
                                </div>
                                <p class="mt-1.5 text-xs text-slate-400">Huruf kecil, angka, dan tanda penghubung</p>
                                @error('subdomain')
                                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Logo --}}
                            <div class="mt-5">
                                <label class="mb-1.5 block text-sm font-semibold text-slate-800">Logo</label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-full border-2 border-blue-400 px-5 py-3 text-sm font-semibold text-blue-500 transition hover:bg-blue-50">
                                    <template x-if="!logoPreview">
                                        <span class="flex items-center gap-2">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                            Upload File Logo
                                        </span>
                                    </template>
                                    <template x-if="logoPreview">
                                        <span class="flex items-center gap-2">
                                            <img :src="logoPreview" class="h-6 w-6 rounded-full object-cover ring-1 ring-blue-300">
                                            Ganti Logo
                                        </span>
                                    </template>
                                    <input type="file" name="logo" accept="image/png,image/jpeg,image/webp"
                                           class="sr-only" @change="handleLogo($event)">
                                </label>
                                <p class="mt-1.5 text-xs text-slate-400">PNG, JPG (maks. 2 MB)</p>
                                @error('logo')
                                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Right: Personalisasi Kasir --}}
                    <div>
                        <h2 class="mb-4 text-2xl font-bold tracking-tight text-slate-900">Personalisasi Kasir</h2>
                        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                            <input type="hidden" name="layout" :value="activeLayout">
                            <input type="hidden" name="theme" :value="activeTheme">
                            <input type="hidden" name="color_palette" :value="activePalette">

                            {{-- Layout --}}
                            <div>
                                <p class="mb-3 text-sm font-semibold text-slate-800">Layout</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($layouts as $key => $layout)
                                        <button type="button"
                                                @click="activeLayout = '{{ $key }}'"
                                                :class="activeLayout === '{{ $key }}'
                                                    ? 'bg-blue-500 text-white border-blue-500'
                                                    : 'bg-white text-slate-700 border-slate-300 hover:border-blue-300'"
                                                class="rounded-full border-2 px-5 py-2 text-sm font-medium transition">
                                            {{ $layout['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                                {{-- Wireframe preview --}}
                                <div class="mt-4 max-w-[200px]">
                                    @foreach ($layouts as $key => $layout)
                                        <div x-show="activeLayout === '{{ $key }}'" style="{{ $currentLayout === $key ? '' : 'display:none' }}">
                                            <x-layout-wireframe :type="$key" />
                                        </div>
                                    @endforeach
                                </div>
                                @error('layout')
                                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Tema --}}
                            <div class="mt-6">
                                <p class="mb-3 text-sm font-semibold text-slate-800">Tema</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($themes as $key => $theme)
                                        <button type="button"
                                                @click="changeTheme('{{ $key }}')"
                                                :class="activeTheme === '{{ $key }}'
                                                    ? 'bg-blue-500 text-white border-blue-500'
                                                    : 'bg-white text-slate-700 border-slate-300 hover:border-blue-300'"
                                                class="rounded-full border-2 px-5 py-2 text-sm font-medium transition">
                                            {{ $theme['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                                @error('theme')
                                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Palet Warna --}}
                            <div class="mt-6">
                                <p class="mb-3 text-sm font-semibold text-slate-800">Palet Warna</p>
                                @foreach ($themes as $themeKey => $theme)
                                    <div x-show="activeTheme === '{{ $themeKey }}'" style="{{ $currentTheme === $themeKey ? '' : 'display:none' }}"
                                         class="flex flex-wrap gap-3">
                                        @foreach ($theme['palettes'] as $paletteKey)
                                            @php $p = $palettes[$paletteKey] @endphp
                                            <button type="button"
                                                    @click="activePalette = '{{ $paletteKey }}'"
                                                    class="group flex flex-col items-center gap-1">
                                                <span class="relative flex h-11 w-11 items-center justify-center rounded-xl transition"
                                                      :class="activePalette === '{{ $paletteKey }}' ? 'ring-2 ring-blue-500 ring-offset-2' : 'ring-1 ring-slate-200 hover:ring-blue-300'"
                                                      style="background-color: {{ $p['--brand-primary'] }}">
                                                    <span x-show="activePalette === '{{ $paletteKey }}'"
                                                          class="absolute inset-0 flex items-center justify-center">
                                                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </span>
                                                </span>
                                                <span class="text-[10px] text-slate-500">{{ ucfirst($paletteKey) }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endforeach
                                @error('color_palette')
                                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="mt-10 flex justify-center">
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-full bg-lime-400 px-8 py-3.5 text-base font-bold text-slate-900 shadow-sm transition hover:bg-lime-500 active:scale-95">
                        Buat Toko
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 1.5L14.09 8.26L21 9.27L16 14.14L17.18 21.02L12 17.77L6.82 21.02L8 14.14L3 9.27L9.91 8.26L12 1.5Z"/>
                            <path d="M5 3.5L5.74 5.76L8 6.5L5.74 7.24L5 9.5L4.26 7.24L2 6.5L4.26 5.76L5 3.5Z" opacity=".7"/>
                            <path d="M19 1.5L19.74 3.76L22 4.5L19.74 5.24L19 7.5L18.26 5.24L16 4.5L18.26 3.76L19 1.5Z" opacity=".7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <style>
        .progress-ball {
            animation: progress-move 3s ease-out forwards;
        }
        @keyframes progress-move {
            0%   { left: 0px; }
            50%  { left: calc(100% - 7rem); }
            75%  { left: calc(100% - 4rem); }
            100% { left: calc(100% - 2.5rem); }
        }
    </style>
</x-app-layout>
