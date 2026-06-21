<x-tenant-page>
@php
    $currentTheme   = old('theme', $tenant->theme());
    $currentPalette = old('color_palette', $tenant->colorPalette());
    $currentLayout  = old('layout', $tenant->layout());

    // Toleransi data lama: jika theme/layout tersimpan tidak lagi terdaftar di
    // config branding (mis. dari seeder/template lama), pakai default valid agar
    // kartu pilihan tetap ter-select.
    if (! isset($themes[$currentTheme])) {
        $currentTheme = array_key_first($themes);
    }
    if (! isset($layouts[$currentLayout])) {
        $currentLayout = array_key_first($layouts);
    }

    // Ensure currentPalette is valid for currentTheme on load
    $validPalettes = collect($themes)->map(fn($t) => $t['palettes']);
    if (! in_array($currentPalette, $themes[$currentTheme]['palettes'] ?? [], true)) {
        $currentPalette = $themes[$currentTheme]['palettes'][0] ?? 'violet';
    }
@endphp

<div class="max-w-2xl mx-auto px-4 py-8"
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

    <h1 class="text-2xl font-bold mb-6">Pengaturan Toko</h1>

    @if (session('status') === 'settings-updated')
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-sm text-green-700">
            Pengaturan berhasil disimpan.
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST"
          action="{{ route('tenant.settings.update', ['subdomain' => $tenant->subdomain]) }}"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Identitas --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
            <h2 class="font-semibold text-gray-700">Identitas Toko</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Toko</label>
                <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subdomain</label>
                <div class="flex items-center gap-2">
                    <input type="text" name="subdomain" value="{{ old('subdomain', $tenant->subdomain) }}" required
                        class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="text-sm text-gray-400">.kasiro.com</span>
                </div>
                <p class="mt-1 text-xs text-amber-600">Mengubah subdomain akan mengubah URL toko kamu.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                @if ($tenant->logo_path)
                    <img src="{{ asset('storage/'.$tenant->logo_path) }}" alt="Logo"
                        class="h-16 w-16 rounded-lg object-cover mb-2 border">
                @endif
                <input type="file" name="logo" accept="image/*"
                    class="text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="mt-1 text-xs text-gray-400">Maks. 2MB. Kosongkan jika tidak ingin mengganti.</p>
            </div>
        </div>

        {{-- Layout (structural) --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="font-semibold text-gray-700 mb-1">Layout</h2>
            <p class="text-xs text-gray-400 mb-3">Posisi navigasi dan susunan halaman.</p>
            <div class="grid grid-cols-3 gap-3">
                @foreach ($layouts as $key => $layout)
                    <label class="cursor-pointer relative block">
                        <input type="radio" name="layout" value="{{ $key }}" class="sr-only peer"
                            @checked($currentLayout === $key)>
                        <div class="border-2 rounded-lg p-3 transition
                                    peer-checked:border-indigo-500 peer-checked:bg-indigo-50
                                    border-gray-200 hover:border-gray-300">
                            <x-layout-wireframe :type="$key" />
                            <p class="mt-2 text-sm font-medium text-gray-700 text-center">{{ $layout['label'] }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Theme (visual style) --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="font-semibold text-gray-700 mb-1">Tema</h2>
            <p class="text-xs text-gray-400 mb-3">Gaya tipografi dan karakter visual toko.</p>
            {{-- Hidden input carries the submitted theme; cards are Alpine-driven --}}
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
        </div>

        {{-- Color Palette (per theme, dynamic) --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="font-semibold text-gray-700 mb-1">Warna Brand</h2>
            <p class="text-xs text-gray-400 mb-3">Pilihan warna berdasarkan tema yang dipilih.</p>

            {{-- Hidden input carries the actual submitted value --}}
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
        </div>

        <button type="submit"
            class="w-full py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition">
            Simpan Pengaturan
        </button>
    </form>
</div>
</x-tenant-page>
