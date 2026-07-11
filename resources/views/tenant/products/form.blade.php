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

<x-tenant-page>
    <div class="p-6"
         x-data="{
             imagePreview: @js(!empty($product->image_path) ? asset('storage/'.$product->image_path) : null),
             imageCropSrc: null,
             showCropModal: false,
             cropper: null,

             onImageSelect(e) {
                 const f = e.target.files[0];
                 if (!f) return;
                 this.imageCropSrc = URL.createObjectURL(f);
                 this.showCropModal = true;
                 this.$nextTick(() => {
                     const img = document.getElementById('product-crop-img');
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
                 this.cropper.getCroppedCanvas({ width: 600, height: 600 }).toBlob((blob) => {
                     const file = new File([blob], 'product.png', { type: 'image/png' });
                     const dt = new DataTransfer();
                     dt.items.add(file);
                     document.getElementById('product-image-input').files = dt.files;
                     this.imagePreview = URL.createObjectURL(blob);
                     this.closeCropModal();
                 }, 'image/png');
             },
             cancelCrop() {
                 document.getElementById('product-image-input').value = '';
                 this.closeCropModal();
             },
             closeCropModal() {
                 if (this.cropper) { this.cropper.destroy(); this.cropper = null; }
                 this.showCropModal = false;
                 this.imageCropSrc = null;
             }
         }">
        <div class="max-w-lg">
            <h1 class="text-xl font-semibold mb-6">
                {{ isset($product) ? __('Edit Produk') : __('Tambah Produk') }}
            </h1>

            <form method="POST" data-loading
                action="{{ isset($product)
                    ? route('tenant.products.update', ['subdomain' => $tenant->subdomain, 'product' => $product])
                    : route('tenant.products.store', ['subdomain' => $tenant->subdomain]) }}"
                enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
                @csrf
                @if (isset($product))
                    @method('PUT')
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Nama Produk') }} <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}"
                        placeholder="{{ __('Contoh: Kopi Susu Gula Aren') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required maxlength="200">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Kategori') }}</label>
                    <select name="category_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">{{ __('— Tanpa Kategori —') }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Foto Produk') }}</label>
                    <div class="flex flex-col items-start gap-2">
                        <label for="product-image-input" class="relative cursor-pointer group block">
                            <div class="h-24 w-24 rounded-lg overflow-hidden border transition-all duration-200"
                                 :class="imagePreview ? 'bg-white' : 'border-dashed border-gray-300 bg-gray-50 group-hover:border-gray-400'">
                                <template x-if="imagePreview">
                                    <img :src="imagePreview" alt="{{ __('Foto Produk') }}" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!imagePreview">
                                    <div class="flex h-full w-full flex-col items-center justify-center gap-1">
                                        <svg class="h-7 w-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                </template>
                            </div>
                            <div class="absolute inset-0 rounded-lg bg-black/50 flex flex-col items-center justify-center gap-1
                                        opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-white text-[10px] font-medium">{{ __('Ganti') }}</span>
                            </div>
                        </label>
                        <input id="product-image-input" name="image" type="file" accept="image/*" class="hidden"
                               x-on:change.stop="onImageSelect($event)">
                    </div>
                    <p class="mt-1 text-xs text-gray-400">{{ __('Maks. 2MB. Kosongkan jika tidak ingin mengganti.') }}</p>
                    @error('image')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Harga (Rp)') }} <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="price" value="{{ old('price', $product->price ?? 0) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            min="0" step="100" required>
                        @error('price')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Stok') }} <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            min="0" required>
                        @error('stock')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-sm font-medium text-gray-700">{{ __('Produk Aktif') }}</label>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">{{ __('Simpan') }}</button>
                    <a href="{{ route('tenant.products.index', ['subdomain' => $tenant->subdomain]) }}"
                        class="px-5 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-50 transition">{{ __('Batal') }}</a>
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
                        <h3 class="font-semibold text-gray-800">{{ __('Crop Foto Produk') }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ __('Geser & resize kotak untuk menyesuaikan area') }}</p>
                    </div>
                    <button type="button" x-on:click="cancelCrop()"
                            class="h-8 w-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="bg-gray-900 overflow-hidden" style="height: 300px; position: relative;">
                    <img id="product-crop-img" :src="imageCropSrc" alt="Crop"
                         style="display: block; max-width: 100%; max-height: 300px;">
                </div>
                <div class="flex items-center gap-3 px-6 py-4 border-t justify-end">
                    <button type="button" x-on:click="cancelCrop()"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50 transition">
                        {{ __('Batal') }}
                    </button>
                    <button type="button" x-on:click="confirmCrop()"
                            class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                        {{ __('Terapkan') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-tenant-page>
