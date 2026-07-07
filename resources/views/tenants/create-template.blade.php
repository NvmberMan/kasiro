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
        // Preselect from a validation redisplay (old) or a ?template=slug deep link
        // (e.g. clicking a template card on the landing page).
        $selectedTemplate = old('template_id')
            ? $templates->firstWhere('id', (int) old('template_id'))
            : $templates->firstWhere('slug', request('template'));
        $selectedTemplateId = $selectedTemplate?->id;
    @endphp

    <div x-data="{
            loading: false,
            pickerOpen: false,
            selectedId: @js($selectedTemplateId ? (int) $selectedTemplateId : null),
            selectedName: @js($selectedTemplate?->name ?? null),
            selectedThumb: @js($selectedTemplate?->screenshotUrl()),
            selectTemplate(id, name, thumb) {
                this.selectedId = id;
                this.selectedName = name;
                this.selectedThumb = thumb || null;
                this.pickerOpen = false;
            },
            logoPreview: null,
            logoCropSrc: null,
            showCropModal: false,
            cropper: null,
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
        <div x-show="loading"
             style="display:none"
             class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-[#f3f4f3]">
            <img src="{{ asset('images/kasiro-logo-black.png') }}" alt="Kasiro" class="mb-10 h-12 w-auto">
            <div class="h-7 w-80 overflow-hidden rounded-full border-2 border-[#c0c6ef] bg-[#eaf3c9]">
                <div class="progress-fill h-full rounded-full bg-[#2734bd]"></div>
            </div>
            <p class="mt-4 text-sm text-slate-500">Loading...</p>
        </div>

        {{-- Template Picker Modal --}}
        <div x-show="pickerOpen"
             style="display:none"
             class="fixed inset-0 z-40 flex flex-col bg-white overflow-y-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4">
            <div class="max-w-3xl mx-auto w-full px-4 py-8">
                {{-- Header --}}
                <div class="mb-8 flex items-center gap-3">
                    <button type="button" @click="pickerOpen = false"
                            class="flex items-center gap-1.5 text-slate-700 hover:text-slate-900 font-medium text-sm transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <h2 class="text-2xl font-bold text-slate-900">Template Kasir</h2>
                </div>

                @if ($templates->isEmpty())
                    <p class="text-center text-slate-400 py-16">Belum ada template tersedia.</p>
                @else
                    <div class="grid grid-cols-2 gap-5">
                        @foreach ($templates as $template)
                            <button type="button"
                                    @click="selectTemplate({{ $template->id }}, '{{ addslashes($template->name) }}', '{{ $template->screenshotUrl() }}')"
                                    class="group text-left">
                                <div class="overflow-hidden rounded-2xl border-2 transition"
                                     :class="selectedId === {{ $template->id }} ? 'border-blue-500' : 'border-slate-200 hover:border-blue-300'">
                                    {{-- Thumbnail: real screenshot or checkerboard --}}
                                    @if ($template->screenshotUrl())
                                        <img src="{{ $template->screenshotUrl() }}"
                                             alt="{{ $template->name }}"
                                             class="aspect-video w-full object-cover object-top">
                                    @else
                                        <div class="aspect-video w-full"
                                             style="background-image: repeating-conic-gradient(#e5e7eb 0% 25%, #f3f4f6 0% 50%); background-size: 24px 24px;">
                                        </div>
                                    @endif
                                </div>
                                <p class="mt-2 font-semibold text-slate-900">{{ $template->name }}</p>
                                {{-- Always reserve 2 lines so cards align regardless of description length --}}
                                <p class="text-xs text-slate-400 line-clamp-2 min-h-[2rem]">{{ $template->description }}</p>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.assign('{{ route('tenants.choose') }}')"
                    aria-label="Kembali"
                    class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-600 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-900">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
        </div>

        {{-- Main form --}}
        <div class="mx-auto max-w-lg px-4 pt-5 sm:px-6">
            <h2 class="mb-5 text-2xl font-bold tracking-tight text-slate-900">Rincian Kasir</h2>

            <form method="POST" action="{{ route('tenants.store.template') }}"
                  enctype="multipart/form-data"
                  @submit="loading = true">
                @csrf

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">

                    {{-- Template selection --}}
                    <div class="mb-5">
                        <label class="mb-1.5 block text-sm font-semibold text-slate-800">Sistem Template</label>
                        <input type="hidden" name="template_id" :value="selectedId">

                        {{-- Empty state: pill prompt --}}
                        <button type="button" @click="pickerOpen = true" x-show="!selectedId"
                                class="w-full rounded-full border-2 border-blue-400 px-5 py-3 text-sm font-semibold text-blue-500 transition hover:bg-blue-50">
                            Pilih template
                        </button>

                        {{-- Selected state: screenshot + name --}}
                        <button type="button" @click="pickerOpen = true" x-show="selectedId" x-cloak
                                class="w-full overflow-hidden rounded-2xl border-2 border-blue-400 text-left transition hover:border-blue-500">
                            <div class="aspect-video w-full overflow-hidden bg-slate-100"
                                 style="background-image: repeating-conic-gradient(#e5e7eb 0% 25%, #f3f4f6 0% 50%); background-size: 24px 24px;">
                                <img :src="selectedThumb" x-show="selectedThumb" alt=""
                                     class="h-full w-full object-cover object-top">
                            </div>
                            <div class="flex items-center justify-between px-4 py-3">
                                <span class="text-sm font-semibold text-blue-600" x-text="selectedName"></span>
                                <span class="text-xs font-medium text-slate-400">Ganti</span>
                            </div>
                        </button>
                        @error('template_id')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

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
                                   class="min-w-0 flex-1 rounded-l-full border border-r-0 border-slate-300 px-5 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:z-10">
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
                            <input id="logo-file-input" type="file" name="logo" accept="image/png,image/jpeg,image/webp"
                                   class="sr-only" @change="onLogoSelect($event)">
                        </label>
                        <p class="mt-1.5 text-xs text-slate-400">PNG, JPG (maks. 2 MB)</p>
                        @error('logo')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Submit --}}
                <div class="mt-8 flex justify-center">
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
            </form>
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
    </style>
</x-app-layout>
