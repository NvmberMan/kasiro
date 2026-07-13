{{--
    Konteks Microsoft Clarity. Project ID dibaca saat runtime dari config (bukan
    lewat VITE_* yang ter-bake saat `npm run build`), supaya ID/on-off bisa diubah
    cukup dengan mengedit .env di server tanpa perlu build ulang asset.
    Inisialisasi & identify-nya ada di resources/js/clarity.js.
--}}
@if (config('services.clarity.enabled') && config('services.clarity.project_id'))
    <meta name="clarity-project-id" content="{{ config('services.clarity.project_id') }}">
    @auth
        <meta name="clarity-user-id" content="{{ auth()->id() }}">
    @endauth
    @isset($tenant)
        <meta name="clarity-tenant" content="{{ $tenant->subdomain }}">
    @endisset
@endif
