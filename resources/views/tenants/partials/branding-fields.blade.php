{{-- Shared name / subdomain / logo fields used by all three create-tenant forms --}}

<div>
    <x-input-label for="name" :value="__('Nama Toko')" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
        :value="old('name')" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="subdomain" :value="__('Subdomain')" />
    <div class="mt-1 flex rounded-md shadow-sm">
        <x-text-input id="subdomain" name="subdomain" type="text" class="block w-full rounded-r-none"
            :value="old('subdomain')" required placeholder="namatoko" />
        <span class="inline-flex items-center rounded-r-md border border-l-0 border-gray-300 bg-gray-50 px-3 text-gray-500 text-sm">
            .{{ config('tenancy.central_domain') }}
        </span>
    </div>
    <p class="mt-1 text-xs text-gray-500">Huruf kecil, angka, dan tanda hubung. Contoh: <code>warung-budi</code></p>
    <x-input-error :messages="$errors->get('subdomain')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="logo" :value="__('Logo (opsional)')" />
    <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp"
        class="mt-1 block w-full text-sm text-gray-500
               file:mr-4 file:py-2 file:px-4 file:rounded-md
               file:border-0 file:text-sm file:font-semibold
               file:bg-indigo-50 file:text-indigo-700
               hover:file:bg-indigo-100" />
    <p class="mt-1 text-xs text-gray-500">PNG, JPG, WebP — maks. 2 MB</p>
    <x-input-error :messages="$errors->get('logo')" class="mt-2" />
</div>
