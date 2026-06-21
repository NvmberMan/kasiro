{{--
    Modal konfirmasi global. Form mana pun yang memiliki atribut data-confirm akan
    menampilkan modal ini alih-alih dialog confirm() bawaan browser.

    Penggunaan pada <form>:
      data-confirm="Pesan konfirmasi."        (wajib — memicu modal)
      data-confirm-title="Judul"              (opsional)
      data-confirm-action="Ya, Lanjutkan"     (opsional — teks tombol konfirmasi)
      data-confirm-type="danger|primary"      (opsional — warna; default danger)
--}}
<style>[x-cloak]{display:none !important}</style>
<div x-data="confirmModal()" @open-confirm.window="open($event.detail)" x-cloak>
    <div x-show="show" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        {{-- Backdrop --}}
        <div x-show="show" x-transition.opacity
             class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="cancel()"></div>

        {{-- Dialog --}}
        <div x-show="show"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-90 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @keydown.escape.window="cancel()"
             class="relative w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-2xl">

            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full"
                 :class="type === 'primary' ? 'bg-indigo-100' : 'bg-red-100'">
                <svg x-show="type === 'primary'" class="h-7 w-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <svg x-show="type !== 'primary'" class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>

            <h3 class="text-lg font-semibold text-gray-900" x-text="title"></h3>
            <p class="mt-2 text-sm text-gray-500" x-text="message"></p>

            <div class="mt-6 flex gap-3">
                <button type="button" @click="cancel()"
                        class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    Batal
                </button>
                <button type="button" @click="confirm()" x-text="confirmText"
                        class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition"
                        :class="type === 'primary' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-red-600 hover:bg-red-700'"></button>
            </div>
        </div>
    </div>
</div>
<script>
    function confirmModal() {
        return {
            show: false,
            title: 'Konfirmasi',
            message: '',
            confirmText: 'Ya, Lanjutkan',
            type: 'danger',
            form: null,
            open(d) {
                this.title = d.title || 'Konfirmasi';
                this.message = d.message || 'Apakah Anda yakin?';
                this.confirmText = d.confirmText || 'Ya, Lanjutkan';
                this.type = d.type || 'danger';
                this.form = d.form || null;
                this.show = true;
            },
            cancel() {
                this.show = false;
                this.form = null;
            },
            confirm() {
                const f = this.form;
                this.show = false;
                if (f) {
                    f.dataset.confirmed = '1';
                    f.submit(); // tidak memicu event 'submit', jadi tidak dicegat lagi
                }
            },
        };
    }

    // Cegat submit form ber-atribut data-confirm dan tampilkan modal.
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form.matches('[data-confirm]') && form.dataset.confirmed !== '1') {
            e.preventDefault();
            window.dispatchEvent(new CustomEvent('open-confirm', {
                detail: {
                    form: form,
                    message: form.dataset.confirm,
                    title: form.dataset.confirmTitle,
                    confirmText: form.dataset.confirmAction,
                    type: form.dataset.confirmType,
                },
            }));
        }
    }, true);
</script>
