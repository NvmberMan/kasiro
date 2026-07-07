<x-app-layout :full-width="true">
    @push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <style>
        .cropper-wrap-box, .cropper-canvas, .cropper-drag-box, .cropper-crop-box { max-height: 300px; }
        .cropper-container { max-height: 300px !important; }
    </style>
    @endpush
    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    @endpush

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
            step: 1,
            stepTitles: { 1: 'Personalisasi Kasir', 2: 'Rincian Kasir' },
            fields: { name: @js(old('name', '')), subdomain: @js(old('subdomain', '')) },
            errors: { name: '', subdomain: '' },
            next() { if (this.step < 2) this.step++; window.scrollTo({ top: 0, behavior: 'smooth' }); },
            back() {
                if (this.step > 1) { this.step--; window.scrollTo({ top: 0, behavior: 'smooth' }); }
                else { window.location = '{{ route('tenants.choose') }}'; }
            },
            validateName() {
                return this.fields.name.trim() === '' ? 'Nama toko wajib diisi.' : '';
            },
            validateSubdomain() {
                const v = this.fields.subdomain.trim();
                if (v === '') return 'Nama domain wajib diisi.';
                if (!/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/.test(v)) return 'Hanya huruf kecil, angka, dan tanda penghubung; harus diawali dan diakhiri huruf atau angka.';
                if (v.length < 3) return 'Nama domain minimal 3 karakter.';
                if (v.length > 63) return 'Nama domain maksimal 63 karakter.';
                return '';
            },
            submitForm(e) {
                this.errors.name = this.validateName();
                this.errors.subdomain = this.validateSubdomain();
                if (this.errors.name || this.errors.subdomain) {
                    e.preventDefault();
                    this.step = 2;
                    return;
                }
                this.loading = true;
            },
            activeLayout: @js($currentLayout),
            activeTheme: @js($currentTheme),
            activePalette: @js($currentPalette),
            themePalettes: @js($validPalettes),
            allPalettes: @js($palettes),
            allThemes: @js(collect($themes)->map(fn($t) => $t['vars'])),
            logoPreview: null,
            logoCropSrc: null,
            showCropModal: false,
            cropper: null,
            get c() { return this.allPalettes[this.activePalette] ?? {}; },
            get primary()  { return this.c['--brand-primary']  ?? '#6366f1'; },
            get bg()       { return this.c['--brand-bg']       ?? '#f5f5f5'; },
            get surface()  { return this.c['--brand-surface']  ?? '#ffffff'; },
            get border()   { return this.c['--brand-border']   ?? '#e2e8f0'; },
            get fg()       { return this.c['--brand-fg']       ?? '#0f172a'; },
            get muted()    { return this.c['--brand-muted']    ?? '#64748b'; },
            get font()     { return (this.allThemes[this.activeTheme] ?? {})['--brand-font']   ?? 'system-ui'; },
            get radius()   { return (this.allThemes[this.activeTheme] ?? {})['--brand-radius'] ?? '0.875rem'; },
            get previewWrapStyle() {
                if (this.activeTheme === 'retro')   return `border-radius:0; border:2px solid ${this.fg}50; box-shadow:4px 4px 0 ${this.fg}20;`;
                if (this.activeTheme === 'classic') return `border-radius:0; box-shadow:inset -1px -1px #0a0a0a, inset 1px 1px #ffffff, inset -2px -2px #808080, inset 2px 2px #dfdfdf;`;
                return `border-radius:16px; box-shadow:0 4px 20px ${this.primary}25;`;
            },
            get cardStyle() {
                if (this.activeTheme === 'retro')   return `background-color:${this.surface}; border-radius:0; border:1.5px solid ${this.fg}60; box-shadow:2px 2px 0 ${this.fg}20;`;
                if (this.activeTheme === 'classic') return `background-color:${this.surface}; border-radius:0; box-shadow:inset -1px -1px #0a0a0a, inset 1px 1px #ffffff, inset -2px -2px #808080, inset 2px 2px #dfdfdf;`;
                return `background-color:${this.surface}; border-radius:${this.radius}; border:none; box-shadow:0 1px 4px ${this.fg}12;`;
            },
            get bayarStyle() {
                if (this.activeTheme === 'retro')   return `background-color:${this.primary}; color:white; border-radius:0; border:1.5px solid ${this.fg}60; box-shadow:2px 2px 0 ${this.fg}30;`;
                if (this.activeTheme === 'classic') return `background-color:${this.primary}; color:white; border-radius:0; box-shadow:inset -1px -1px #0a0a0a, inset 1px 1px #ffffff, inset -2px -2px #808080, inset 2px 2px #dfdfdf;`;
                return `background-color:${this.primary}; color:white; border-radius:9999px;`;
            },
            get navItemStyle() {
                if (this.activeTheme === 'retro')   return `background:rgba(255,255,255,0.10); border-radius:0; border:1px solid rgba(255,255,255,0.45);`;
                if (this.activeTheme === 'classic') return `background:rgba(255,255,255,0.15); border-radius:0;`;
                return `background:rgba(255,255,255,0.22); border-radius:9999px;`;
            },
            get activeNavStyle() {
                if (this.activeTheme === 'retro')   return `background:rgba(255,255,255,0.30); border-radius:0; border:1px solid rgba(255,255,255,0.7);`;
                if (this.activeTheme === 'classic') return `background:rgba(0,0,0,0.14); border-radius:0; box-shadow:inset 1px 1px rgba(0,0,0,0.3), inset -1px -1px rgba(255,255,255,0.4);`;
                return `background:rgba(255,255,255,0.35); border-radius:9999px;`;
            },
            get chipStyle() {
                if (this.activeTheme === 'retro')   return `border-radius:0; border:1px solid ${this.fg}50; background:${this.surface};`;
                if (this.activeTheme === 'classic') return `border-radius:0; background:${this.surface}; box-shadow:inset 1px 1px #808080, inset -1px -1px #ffffff;`;
                return `border-radius:9999px; background:${this.primary}20;`;
            },
            get activeChipStyle() {
                if (this.activeTheme === 'retro')   return `border-radius:0; border:1px solid ${this.primary}; background:${this.primary}; color:white;`;
                if (this.activeTheme === 'classic') return `border-radius:0; background:${this.surface}; box-shadow:inset -1px -1px #808080, inset 1px 1px #ffffff; color:${this.primary}; font-weight:700;`;
                return `border-radius:9999px; background:${this.primary}; color:white;`;
            },
            changeTheme(t) {
                this.activeTheme = t;
                const ps = this.themePalettes[t] ?? [];
                if (!ps.includes(this.activePalette)) this.activePalette = ps[0] ?? '';
            },
            onLogoSelect(e) {
                const f = e.target.files[0];
                if (!f) return;
                this.logoCropSrc = URL.createObjectURL(f);
                this.showCropModal = true;
                this.$nextTick(() => {
                    const img = document.getElementById('logo-crop-img');
                    if (this.cropper) this.cropper.destroy();
                    this.cropper = new Cropper(img, {
                        aspectRatio: 1, viewMode: 2, dragMode: 'move', autoCropArea: 1,
                        restore: false, guides: true, center: true, highlight: false,
                        minContainerHeight: 300, minContainerWidth: 100,
                    });
                });
            },
            confirmCrop() {
                if (!this.cropper) return;
                this.cropper.getCroppedCanvas({ width: 400, height: 400 }).toBlob((blob) => {
                    const file = new File([blob], 'logo.png', { type: 'image/png' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    document.getElementById('logo-file-input').files = dt.files;
                    this.logoPreview = URL.createObjectURL(blob);
                    this.closeCropModal();
                }, 'image/png');
            },
            cancelCrop() {
                document.getElementById('logo-file-input').value = '';
                this.closeCropModal();
            },
            closeCropModal() {
                if (this.cropper) { this.cropper.destroy(); this.cropper = null; }
                this.showCropModal = false;
                this.logoCropSrc = null;
            }
        }" class="py-4">

        {{-- Loading overlay --}}
        <div x-show="loading" style="display:none"
             class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-[#f3f4f3]">
            <img src="{{ asset('images/kasiro-logo-black.png') }}" alt="Kasiro" class="mb-10 h-12 w-auto">
            <div class="h-7 w-80 overflow-hidden rounded-full border-2 border-[#c0c6ef] bg-[#eaf3c9]">
                <div class="progress-fill h-full rounded-full bg-[#2734bd]"></div>
            </div>
            <p class="mt-4 text-sm text-slate-500">Loading...</p>
        </div>

        <form method="POST" action="{{ route('tenants.store.custom') }}"
              enctype="multipart/form-data"
              @submit="submitForm($event)">
            @csrf

            <input type="hidden" name="layout" :value="activeLayout">
            <input type="hidden" name="theme" :value="activeTheme">
            <input type="hidden" name="color_palette" :value="activePalette">

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <div class="mx-auto max-w-3xl">
                    {{-- Wizard header: back / title / next-or-finish --}}
                    <div class="grid grid-cols-3 items-center">
                        <button type="button" @click="back()" aria-label="Kembali"
                                class="flex h-9 w-9 flex-shrink-0 items-center justify-center justify-self-start rounded-full text-slate-500 transition hover:bg-slate-100">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>

                        <h1 class="justify-self-center text-lg font-bold text-slate-900">Buat Kasir</h1>

                        <div class="justify-self-end">
                            <button type="button" x-show="step < 2" @click="next()" style="display:none"
                                    class="rounded-full bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200">
                                Selanjutnya
                            </button>
                            <button type="submit" x-show="step === 2" style="display:none"
                                    class="inline-flex items-center gap-2 rounded-full bg-lime-400 px-5 py-2.5 text-sm font-bold text-slate-900 transition hover:bg-lime-500 active:scale-95">
                                Selesai!
                            </button>
                        </div>
                    </div>

                    {{-- Step indicator --}}
                    <div class="mt-3 flex items-center justify-center">
                        <template x-for="n in 2" :key="n">
                            <div class="flex items-center">
                                <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full text-sm font-semibold transition"
                                     :class="step === n ? 'bg-blue-500 text-white' : (step > n ? 'bg-blue-100 text-blue-600' : 'border-2 border-slate-200 text-slate-400')"
                                     x-text="n"></div>
                                <div x-show="n < 2" class="h-px w-10 border-t-2 border-dashed border-slate-200"></div>
                            </div>
                        </template>
                    </div>

                    <h2 class="mt-2 text-center text-lg font-bold text-slate-900">
                        <span x-text="`Step ${step}.`"></span>
                        <span x-text="stepTitles[step]"></span>
                    </h2>
                </div>

                <div class="mt-4 border-t border-slate-100 pt-4">

                    {{-- Step 1: Personalisasi Kasir (layout + gaya + warna in one 3-column view) --}}
                    <div x-show="step === 1"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="grid grid-cols-1 gap-4 lg:grid-cols-3">

                        {{-- Col 1: Layout --}}
                        <div class="flex h-full flex-col rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                            <p class="mb-2 text-sm font-semibold text-slate-800">Layout</p>
                            <div class="flex flex-1 flex-col justify-between gap-2">
                                @foreach ($layouts as $key => $layout)
                                    <button type="button"
                                            @click="activeLayout = '{{ $key }}'"
                                            :class="activeLayout === '{{ $key }}'
                                                ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-300'
                                                : 'border-slate-200 bg-white hover:border-blue-300'"
                                            class="flex items-center gap-3 rounded-xl border-2 p-3 transition">
                                        <div class="w-16 flex-shrink-0">
                                            <x-layout-wireframe :type="$key" />
                                        </div>
                                        <span :class="activeLayout === '{{ $key }}' ? 'text-blue-600 font-semibold' : 'text-slate-600 font-medium'"
                                              class="text-sm">
                                            {{ $layout['label'] }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                            @error('layout')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        {{-- Col 2: Preview --}}
                        <div class="flex h-full flex-col justify-center rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                            @include('tenants.partials._preview')
                        </div>

                        {{-- Col 3: Gaya (Tema) + Warna (Palet) --}}
                        <div class="flex h-full flex-col gap-4">
                            <div class="flex flex-1 flex-col rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                                <p class="mb-2 text-sm font-semibold text-slate-800">Gaya</p>
                                <div class="flex flex-1 flex-wrap content-center gap-2">
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
                                @error('theme')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="flex flex-1 flex-col rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                                <p class="mb-2 text-sm font-semibold text-slate-800">Warna</p>
                                @foreach ($themes as $themeKey => $theme)
                                    <div x-show="activeTheme === '{{ $themeKey }}'"
                                         style="{{ $currentTheme === $themeKey ? '' : 'display:none' }}"
                                         class="flex flex-1 flex-wrap content-center gap-3">
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
                                @error('color_palette')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Rincian Kasir --}}
                    <div x-show="step === 2"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <div class="mx-auto max-w-xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">

                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-800">Nama Toko</label>
                                <input type="text" name="name" x-model="fields.name"
                                       @input="errors.name = ''"
                                       placeholder="Contoh: Warung Budi"
                                       class="w-full rounded-full border border-slate-300 px-5 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                <p x-show="errors.name" x-text="errors.name" style="display:none" class="mt-1.5 text-xs text-red-500"></p>
                                @error('name')<p x-show="!errors.name" class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="mt-5">
                                <label class="mb-1.5 block text-sm font-semibold text-slate-800">Nama Domain Kasir</label>
                                <div class="flex items-center">
                                    <input type="text" name="subdomain" x-model="fields.subdomain"
                                           @input="errors.subdomain = ''"
                                           placeholder="namatoko"
                                           class="min-w-0 flex-1 rounded-l-full border border-r-0 border-slate-300 px-5 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:z-10">
                                    <span class="flex items-center rounded-r-full border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-500 whitespace-nowrap">
                                        .{{ config('tenancy.central_domain') }}
                                    </span>
                                </div>
                                <p class="mt-1.5 text-xs text-slate-400">Huruf kecil, angka, dan tanda penghubung (min. 3 karakter)</p>
                                <p x-show="errors.subdomain" x-text="errors.subdomain" style="display:none" class="mt-1.5 text-xs text-red-500"></p>
                                @error('subdomain')<p x-show="!errors.subdomain" class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="mt-5">
                                <label class="mb-1.5 block text-sm font-semibold text-slate-800">Logo Toko <span class="font-normal text-slate-400">(Opsional)</span></label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-full border-2 border-blue-400 px-5 py-3 text-sm font-semibold text-blue-500 transition hover:bg-blue-50">
                                    <template x-if="!logoPreview">
                                        <span class="flex items-center gap-2">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                            Unggah File Logo
                                        </span>
                                    </template>
                                    <template x-if="logoPreview">
                                        <span class="flex items-center gap-2">
                                            <img :src="logoPreview" class="h-6 w-6 rounded-full object-cover ring-1 ring-blue-300">
                                            Ganti Logo
                                        </span>
                                    </template>
                                    <input id="logo-file-input" type="file" name="logo" accept="image/png,image/jpeg,image/webp"
                                           class="sr-only" @change="onLogoSelect($event)">
                                </label>
                                <p class="mt-1.5 text-xs text-slate-400">PNG, JPG (maks. 2 MB)</p>
                                @error('logo')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Crop Modal --}}
    <div x-show="showCropModal" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4"
         style="background: rgba(0,0,0,0.75);"
         x-on:click="cancelCrop()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden" x-on:click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <div>
                    <h3 class="font-semibold text-gray-800">Crop Logo</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Geser &amp; resize kotak untuk menyesuaikan area</p>
                </div>
                <button type="button" x-on:click="cancelCrop()"
                        class="h-8 w-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="bg-gray-900 overflow-hidden" style="height: 300px; position: relative;">
                <img id="logo-crop-img" :src="logoCropSrc" alt="Crop"
                     style="display: block; max-width: 100%; max-height: 300px;">
            </div>
            <div class="flex items-center gap-3 px-6 py-4 border-t justify-end">
                <button type="button" x-on:click="cancelCrop()"
                        class="px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="button" x-on:click="confirmCrop()"
                        class="px-5 py-2 rounded-xl bg-blue-500 text-white text-sm font-medium hover:bg-blue-600 transition">
                    Terapkan
                </button>
            </div>
        </div>
    </div>
    </div>

    <style>
        .progress-fill {
            width: 0%;
            animation: progress-move 3s ease-out forwards;
        }
        @keyframes progress-move {
            0%   { width: 0%; }
            50%  { width: 55%; }
            75%  { width: 78%; }
            100% { width: 100%; }
        }
        .preview-16x9 {
            aspect-ratio: 16 / 9 !important;
        }
    </style>
</x-app-layout>
