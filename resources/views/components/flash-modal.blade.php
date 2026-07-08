@php
    // Centralized success flash → modal copy. 'checkout-success' is intentionally
    // omitted: the POS page renders its own (richer) success modal.
    $messages = [
        'product-created'      => [__('Produk Ditambahkan'), __('Produk baru berhasil disimpan.')],
        'product-updated'      => [__('Produk Diperbarui'), __('Perubahan produk berhasil disimpan.')],
        'product-deleted'      => [__('Produk Dihapus'), __('Produk berhasil dihapus.')],
        'category-created'     => [__('Kategori Ditambahkan'), __('Kategori baru berhasil disimpan.')],
        'category-updated'     => [__('Kategori Diperbarui'), __('Perubahan kategori berhasil disimpan.')],
        'category-deleted'     => [__('Kategori Dihapus'), __('Kategori berhasil dihapus.')],
        'role-updated'         => [__('Peran Diperbarui'), __('Peran karyawan berhasil diubah.')],
        'employee-revoked'     => [__('Akses Dicabut'), __('Akses karyawan ke toko ini telah dicabut.')],
        'invitation-cancelled' => [__('Undangan Dibatalkan'), __('Undangan berhasil dibatalkan.')],
        'settings-updated'     => [__('Pengaturan Disimpan'), __('Pengaturan toko berhasil diperbarui.')],
        'tenant-archived'      => [__('Toko Diarsipkan'), __('Toko berhasil diarsipkan.')],
        'tenant-restored'      => [__('Toko Dipulihkan'), __('Toko berhasil dipulihkan.')],
        'tenant-deleted'       => [__('Toko Dihapus'), __('Toko berhasil dihapus secara permanen.')],
    ];

    $flash = $messages[session('status')] ?? null;
    $invitationLink = session('invitation_link');
@endphp

@if ($flash)
<div x-data="{ open: true }" x-show="open" style="display:none"
     @keydown.escape.window="open = false"
     class="fixed inset-0 z-[80] flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
    <div x-show="open" x-transition class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
        <div class="mx-auto mb-4 h-14 w-14 rounded-full bg-green-100 flex items-center justify-center">
            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h3 class="text-lg font-bold text-gray-800">{{ $flash[0] }}</h3>
        <p class="text-sm text-gray-500 mt-1">{{ $flash[1] }}</p>
        <button @click="open = false"
                class="mt-5 w-full py-2.5 bg-indigo-600 text-white rounded-xl font-semibold text-sm hover:bg-indigo-700 transition">{{ __('Oke') }}</button>
    </div>
</div>
@endif

@if ($invitationLink)
<div x-data="{ open: true, copied: false, link: @js($invitationLink),
              copy() { navigator.clipboard.writeText(this.link).then(() => { this.copied = true; setTimeout(() => this.copied = false, 2000); }); } }"
     x-show="open" style="display:none"
     @keydown.escape.window="open = false"
     class="fixed inset-0 z-[80] flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
    <div x-show="open" x-transition class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 text-center">
        <div class="mx-auto mb-4 h-14 w-14 rounded-full bg-green-100 flex items-center justify-center">
            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
        </div>
        <h3 class="text-lg font-bold text-gray-800">{{ __('Undangan Dibuat') }}</h3>
        <p class="text-sm text-gray-500 mt-1">{{ __('Bagikan link berikut ke calon karyawan.') }}</p>
        <div class="mt-4 flex items-center gap-2">
            <input type="text" readonly :value="link" @focus="$el.select()"
                   class="flex-1 min-w-0 rounded-lg border border-gray-300 px-3 py-2 text-xs text-gray-600 bg-gray-50">
            <button @click="copy()"
                    class="flex-shrink-0 px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 transition">
                <span x-show="!copied">{{ __('Salin') }}</span>
                <span x-show="copied" style="display:none">{{ __('Tersalin!') }}</span>
            </button>
        </div>
        <button @click="open = false"
                class="mt-5 w-full py-2.5 bg-gray-100 text-gray-700 rounded-xl font-semibold text-sm hover:bg-gray-200 transition">{{ __('Tutup') }}</button>
    </div>
</div>
@endif
