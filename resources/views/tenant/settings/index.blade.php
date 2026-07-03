<x-tenant-page>
@php
    $currentTheme   = old('theme', $tenant->theme());
    $currentPalette = old('color_palette', $tenant->colorPalette());
    $currentLayout  = old('layout', $tenant->layout());

    if (! isset($themes[$currentTheme]))  $currentTheme  = array_key_first($themes);
    if (! isset($layouts[$currentLayout])) $currentLayout = array_key_first($layouts);

    $validPalettes = collect($themes)->map(fn($t) => $t['palettes']);
    if (! in_array($currentPalette, $themes[$currentTheme]['palettes'] ?? [], true)) {
        $currentPalette = $themes[$currentTheme]['palettes'][0] ?? 'violet';
    }
@endphp

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

{{-- The whole settings page is themed live: choosing a theme/palette updates the
     brand CSS variables on this container, so fonts and accent colors below
     reflect the selection in real time (without saving until you click Simpan). --}}
<div class="max-w-3xl mx-auto px-4 py-6"
     :style="previewStyle() + ';font-family: var(--brand-font, inherit)'"
     x-data="{
         activeTheme:   @js($currentTheme),
         activePalette: @js($currentPalette),
         activeLayout:  @js($currentLayout),
         themePalettes: @js($validPalettes),
         allThemeVars:  @js(collect($themes)->map(fn($t) => $t['vars'] ?? [])->toArray()),
         allPaletteVars: @js($palettes),
         dirty: false,
         _initTheme: @js($currentTheme),
         _initPalette: @js($currentPalette),
         _initLayout: @js($currentLayout),
         _initLogoPreview: @js($tenant->logo_path ? asset('storage/'.$tenant->logo_path) : null),
         logoPreview: @js($tenant->logo_path ? asset('storage/'.$tenant->logo_path) : null),
         logoCropSrc: null,
         showCropModal: false,
         cropper: null,

         cancelChanges() {
             this.activeTheme = this._initTheme;
             this.activePalette = this._initPalette;
             this.activeLayout = this._initLayout;
             this.logoPreview = this._initLogoPreview;
             document.getElementById('logo-file-input').value = '';
             document.getElementById('settings-form').reset();
             this.dirty = false;
         },
         changeTheme(t) {
             this.activeTheme = t;
             const ps = this.themePalettes[t] ?? [];
             if (!ps.includes(this.activePalette)) this.activePalette = ps[0] ?? '';
             this.dirty = true;
         },
         previewStyle() {
             const tv = this.allThemeVars[this.activeTheme] ?? {};
             const pv = this.allPaletteVars[this.activePalette] ?? {};
             const vars = {...tv, ...pv};
             Object.entries(vars).forEach(([k, v]) => document.documentElement.style.setProperty(k, v));
             return Object.entries(vars).map(([k,v]) => k+':'+v).join(';');
         },
         selStyle(active) {
             return active
                 ? 'border-color: var(--brand-primary); background: color-mix(in srgb, var(--brand-primary) 9%, white);'
                 : '';
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
                 this.dirty = true;
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
     }">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Pengaturan Toko</h1>
    </div>

    @if ($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" id="settings-form"
          action="{{ route('tenant.settings.update', ['subdomain' => $tenant->subdomain]) }}"
          enctype="multipart/form-data"
          x-on:change.capture="dirty = true"
          class="space-y-6 pb-24">
        @csrf
        @method('PUT')

        {{-- Identitas Toko --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50">
                <h2 class="font-semibold text-gray-800">Identitas Toko</h2>
            </div>
            <div class="p-6">
                <div class="flex flex-col sm:flex-row gap-6">
                    {{-- Logo upload --}}
                    <div class="flex flex-col items-center gap-2 shrink-0">
                        <label for="logo-file-input" class="relative cursor-pointer group block">
                            <div class="h-24 w-24 rounded-2xl overflow-hidden border-2 transition-all duration-200 shadow-sm"
                                 :class="logoPreview ? 'bg-white' : 'border-dashed border-gray-300 bg-gray-50 group-hover:border-gray-400'"
                                 :style="logoPreview ? 'border-color: var(--brand-primary)' : ''">
                                <template x-if="logoPreview">
                                    <img :src="logoPreview" alt="Logo" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!logoPreview">
                                    <div class="flex h-full w-full flex-col items-center justify-center gap-1">
                                        <svg class="h-7 w-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                </template>
                            </div>
                            <div class="absolute inset-0 rounded-2xl bg-black/50 flex flex-col items-center justify-center gap-1
                                        opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-white text-[10px] font-medium">Ganti</span>
                            </div>
                        </label>
                        <input id="logo-file-input" name="logo" type="file" accept="image/*" class="hidden"
                               x-on:change.stop="onLogoSelect($event)">
                        <span class="text-xs text-gray-400 text-center leading-tight">JPG, PNG<br>maks 2MB</span>
                    </div>

                    {{-- Fields --}}
                    <div class="flex-1 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Toko</label>
                            <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                                placeholder="Nama toko kamu"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Subdomain</label>
                            <div class="flex items-center gap-2">
                                <input type="text" name="subdomain" value="{{ old('subdomain', $tenant->subdomain) }}" required
                                    placeholder="nama-toko"
                                    class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition min-w-0">
                                <span class="text-sm text-gray-400 shrink-0">.kasiro.my.id</span>
                            </div>
                            <p class="mt-1.5 text-xs text-amber-600">Mengubah subdomain akan mengubah URL toko kamu.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Pajak (%)</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="tax_percent" min="0" max="100" step="0.01"
                                    value="{{ old('tax_percent', rtrim(rtrim(number_format($tenant->taxPercent(), 2, '.', ''), '0'), '.')) }}"
                                    placeholder="0"
                                    class="w-32 rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                                <span class="text-sm text-gray-400">%</span>
                            </div>
                            <p class="mt-1.5 text-xs text-gray-400">Isi 0 untuk menonaktifkan pajak.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Layout --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50">
                <h2 class="font-semibold text-gray-800">Layout</h2>
                <p class="text-xs text-gray-400 mt-0.5">Posisi navigasi toko · diterapkan setelah disimpan</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach ($layouts as $key => $layout)
                        <label class="cursor-pointer block">
                            <input type="radio" name="layout" value="{{ $key }}" class="sr-only"
                                   x-on:change="activeLayout = '{{ $key }}'"
                                   @checked($currentLayout === $key)>
                            <div class="border-2 rounded-xl p-3 transition-all duration-150 text-center"
                                 :class="activeLayout === '{{ $key }}' ? 'border-transparent shadow-sm' : 'border-gray-200 hover:border-gray-300'"
                                 :style="selStyle(activeLayout === '{{ $key }}')">
                                <x-layout-wireframe :type="$key" />
                                <p class="mt-2 text-xs font-medium text-gray-700">{{ $layout['label'] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Tema --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50">
                <h2 class="font-semibold text-gray-800">Tema</h2>
                <p class="text-xs text-gray-400 mt-0.5">Gaya tipografi · langsung terlihat di halaman ini</p>
            </div>
            <div class="p-6">
                <input type="hidden" name="theme" :value="activeTheme">
                <div class="grid grid-cols-3 gap-3">
                    @foreach ($themes as $key => $theme)
                        <div class="cursor-pointer border-2 rounded-xl p-4 transition-all duration-150"
                             :class="activeTheme === '{{ $key }}' ? 'border-transparent shadow-sm' : 'border-gray-200 hover:border-gray-300'"
                             :style="selStyle(activeTheme === '{{ $key }}')"
                             @click="changeTheme('{{ $key }}')">
                            <p class="text-2xl font-bold text-gray-800 leading-none mb-2"
                               style="font-family: {{ $theme['vars']['--brand-font'] }}">Aa</p>
                            <p class="text-sm font-medium text-gray-700 leading-snug">{{ $theme['label'] }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ count($theme['palettes']) }} warna</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Warna Brand --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50">
                <h2 class="font-semibold text-gray-800">Warna Brand</h2>
                <p class="text-xs text-gray-400 mt-0.5">Warna aksen halaman ikut berubah saat dipilih</p>
            </div>
            <div class="p-6">
                <input type="hidden" name="color_palette" :value="activePalette">
                @foreach ($themes as $themeKey => $theme)
                    <div x-show="activeTheme === '{{ $themeKey }}'"
                         class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach ($theme['palettes'] as $paletteKey)
                            @php $p = $palettes[$paletteKey] @endphp
                            <div class="cursor-pointer border-2 rounded-xl p-3 transition-all duration-150"
                                 :class="activePalette === '{{ $paletteKey }}' ? 'border-transparent shadow-sm' : 'border-gray-200 hover:border-gray-300'"
                                 :style="selStyle(activePalette === '{{ $paletteKey }}')"
                                 @click="activePalette = '{{ $paletteKey }}'; dirty = true">
                                <div class="flex gap-1.5 mb-2">
                                    <span class="h-6 w-6 rounded-full shadow-sm ring-1 ring-black/5" style="background:{{ $p['--brand-primary'] }}"></span>
                                    <span class="h-6 w-6 rounded-full shadow-sm ring-1 ring-black/5" style="background:{{ $p['--brand-accent'] }}"></span>
                                    <span class="h-6 w-6 rounded-full shadow-sm ring-1 ring-black/5" style="background:{{ $p['--brand-bg'] }}"></span>
                                </div>
                                <p class="text-xs font-medium text-gray-700">{{ ucfirst($paletteKey) }}</p>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </form>

    {{-- Sticky save bar (brand-colored) --}}
    <div x-show="dirty" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         :style="previewStyle()"
         class="fixed bottom-0 inset-x-0 z-40 shadow-[0_-8px_40px_rgba(0,0,0,0.2)]">
        <div style="background: var(--brand-primary, #4f46e5);">
            <div class="mx-auto max-w-6xl px-4 py-3.5 flex items-center justify-between gap-4">
                <p class="text-sm text-white font-medium flex items-center gap-2.5">
                    <span class="h-2 w-2 rounded-full bg-white/60 shrink-0 animate-pulse"></span>
                    Ada perubahan yang belum disimpan
                </p>
                <div class="flex items-center gap-2">
                    <button type="button"
                            x-on:click="cancelChanges()"
                            class="px-5 py-2.5 bg-white/20 hover:bg-white/30 rounded-xl text-white text-sm font-medium transition active:scale-95">
                        Batal
                    </button>
                    <button type="submit" form="settings-form"
                            class="px-7 py-2.5 bg-white rounded-xl font-semibold active:scale-95 transition text-sm shadow-md"
                            style="color: var(--brand-primary, #4f46e5);">
                        Simpan Pengaturan
                    </button>
                </div>
            </div>
        </div>
    </div>

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
                        class="px-5 py-2 rounded-xl text-white text-sm font-medium transition"
                        :style="'background: var(--brand-primary, #4f46e5)'">
                    Terapkan
                </button>
            </div>
        </div>
    </div>
</div>
</x-tenant-page>
