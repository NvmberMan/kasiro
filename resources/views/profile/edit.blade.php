<x-app-layout>
@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<style>
    .cropper-wrap-box, .cropper-canvas, .cropper-drag-box, .cropper-crop-box { max-height: 280px; }
    .cropper-container { max-height: 280px !important; }
</style>
@endpush
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
@endpush
    @php
        $user = auth()->user();
        $hasPassword = $user->password !== null;
        $tfEnabled = $user->hasTwoFactorEnabled();
        $tfPending = $user->two_factor_secret && ! $tfEnabled;
        $showCodes = in_array(session('status'), ['two-factor-enabled', 'recovery-codes-generated'], true);
    @endphp

    {{-- Page header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Profil Anda') }}</h1>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_360px]">

        {{-- ── Left column ─────────────────────────────── --}}
        <div class="space-y-6">

            {{-- Personal info --}}
            <div class="rounded-2xl bg-white p-8">
                <h2 class="mb-6 text-lg font-bold text-slate-900">{{ __('Informasi personal') }}</h2>

                {{-- Avatar upload --}}
                <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data"
                      x-data="{
                          preview: @js($user->avatar),
                          changed: false,
                          cropSrc: null,
                          showCrop: false,
                          cropper: null,
                          pick(e) {
                              const f = e.target.files[0];
                              if (!f) return;
                              this.cropSrc = URL.createObjectURL(f);
                              this.showCrop = true;
                              this.$nextTick(() => {
                                  const img = document.getElementById('avatar-crop-img');
                                  if (this.cropper) this.cropper.destroy();
                                  this.cropper = new Cropper(img, {
                                      aspectRatio: 1,
                                      viewMode: 2,
                                      dragMode: 'move',
                                      autoCropArea: 1,
                                      restore: false,
                                      guides: true,
                                      center: true,
                                      highlight: false,
                                      minContainerHeight: 280,
                                      minContainerWidth: 100,
                                  });
                              });
                          },
                          confirmCrop() {
                              if (!this.cropper) return;
                              const canvas = this.cropper.getCroppedCanvas({ width: 400, height: 400 });
                              canvas.toBlob((blob) => {
                                  const file = new File([blob], 'avatar.png', { type: 'image/png' });
                                  const dt = new DataTransfer();
                                  dt.items.add(file);
                                  document.getElementById('avatar-input').files = dt.files;
                                  this.preview = URL.createObjectURL(blob);
                                  this.changed = true;
                                  this.cancelCrop();
                              }, 'image/png');
                          },
                          cancelCrop() {
                              if (this.cropper) { this.cropper.destroy(); this.cropper = null; }
                              this.showCrop = false;
                              this.cropSrc = null;
                              if (!this.changed) document.getElementById('avatar-input').value = '';
                          }
                      }"
                      class="mb-6">
                    @csrf
                    <div class="flex items-center gap-4">
                        <label for="avatar-input" class="relative shrink-0 group cursor-pointer">
                            {{-- Avatar circle --}}
                            <div class="h-16 w-16 rounded-full overflow-hidden ring-2 transition-all duration-300"
                                 :class="changed ? 'ring-[#a4c400] ring-offset-2' : 'ring-slate-200'">
                                <template x-if="preview">
                                    <img :src="preview" alt="Avatar" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!preview">
                                    <span class="flex h-full w-full items-center justify-center bg-slate-200 text-slate-400">
                                        <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                                        </svg>
                                    </span>
                                </template>
                            </div>

                            {{-- Overlay ganti foto --}}
                            <div class="absolute inset-0 rounded-full bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>

                            <input id="avatar-input" name="avatar" type="file" accept="image/*" class="hidden"
                                   x-on:change="pick($event)">
                        </label>

                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ __('Foto Profil') }}</p>
                            <p class="text-xs text-slate-400" x-show="!changed">{{ __('JPG, PNG · maks 2 MB · klik foto untuk ganti & crop') }}</p>
                            <p class="text-xs text-[#a4c400] font-medium" x-show="changed" x-cloak>{{ __('Foto baru dipilih — klik Simpan') }}</p>
                            <button type="submit" x-show="changed" x-cloak
                                    class="mt-2 rounded-full bg-[#a4c400] px-5 py-1.5 text-xs font-semibold text-white hover:bg-[#8fad00] transition">
                                {{ __('Simpan Foto') }}
                            </button>
                            @if ($errors->has('avatar'))
                                <p class="mt-1 text-xs text-red-500">{{ $errors->first('avatar') }}</p>
                            @endif
                            @if (session('status') === 'avatar-updated')
                                <p x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                                   class="mt-1 text-xs text-green-600 font-medium">✓ {{ __('Foto berhasil diperbarui') }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Crop Modal --}}
                    <div x-show="showCrop" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="fixed inset-0 z-[60] flex items-center justify-center p-4"
                         style="background: rgba(0,0,0,0.75);"
                         x-on:click="cancelCrop()">
                        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden"
                             x-on:click.stop>
                            <div class="flex items-center justify-between px-5 py-4 border-b">
                                <div>
                                    <h3 class="font-semibold text-slate-800">{{ __('Crop Foto Profil') }}</h3>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ __('Geser & resize untuk menyesuaikan') }}</p>
                                </div>
                                <button type="button" x-on:click="cancelCrop()"
                                        class="h-8 w-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="bg-gray-900 overflow-hidden" style="height: 280px; position: relative;">
                                <img id="avatar-crop-img" :src="cropSrc" alt="Crop"
                                     style="display: block; max-width: 100%; max-height: 280px;">
                            </div>
                            <div class="flex gap-3 px-5 py-4 border-t justify-end">
                                <button type="button" x-on:click="cancelCrop()"
                                        class="px-4 py-2 rounded-full border border-slate-200 text-sm text-slate-700 hover:bg-slate-50 transition">
                                    {{ __('Batal') }}
                                </button>
                                <button type="button" x-on:click="confirmCrop()"
                                        class="px-5 py-2 rounded-full bg-[#a4c400] text-white text-sm font-semibold hover:bg-[#8fad00] transition">
                                    {{ __('Terapkan') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('patch')

                    <div>
                        <label class="mb-1.5 block text-sm text-slate-700">{{ __('Nama lengkap') }}</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                               placeholder="{{ __('Nama lengkap') }}"
                               class="w-full rounded-full border border-slate-300 px-5 py-3 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                        @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm text-slate-700">{{ __('Email') }}</label>
                        <input type="email" value="{{ $user->email }}" disabled readonly
                               class="w-full rounded-full border border-slate-300 bg-slate-100 px-5 py-3 text-sm text-slate-500 outline-none cursor-not-allowed">
                        <p class="mt-1.5 text-xs text-slate-400">{{ __('Email tidak dapat diubah.') }}</p>
                    </div>

                    <div class="pt-2 flex items-center gap-4">
                        <button type="submit"
                                class="rounded-full bg-[#a4c400] px-7 py-2.5 text-sm font-semibold text-white hover:bg-[#8fad00] transition">
                            {{ __('Simpan') }}
                        </button>
                        @if (session('status') === 'profile-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition
                               x-init="setTimeout(() => show = false, 2000)"
                               class="text-sm text-green-600">{{ __('Tersimpan.') }}</p>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Password --}}
            <div class="rounded-2xl bg-white p-8">
                <h2 class="mb-6 text-lg font-bold text-slate-900">{{ __('Update Password Akun Anda') }}</h2>

                <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    @method('put')

                    @if ($hasPassword)
                        <div>
                            <label class="mb-1.5 block text-sm text-slate-700">{{ __('Current Password') }}</label>
                            <input type="password" name="current_password" placeholder="{{ __('Password saat ini') }}"
                                   class="w-full rounded-full border border-slate-300 px-5 py-3 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                            @if ($errors->updatePassword->has('current_password'))
                                <p class="mt-1 text-xs text-red-500">{{ $errors->updatePassword->first('current_password') }}</p>
                            @endif
                        </div>
                    @endif

                    <div>
                        <label class="mb-1.5 block text-sm text-slate-700">{{ __('New Password') }}</label>
                        <input type="password" name="password" placeholder="{{ __('Password baru') }}"
                               class="w-full rounded-full border border-slate-300 px-5 py-3 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                        @if ($errors->updatePassword->has('password'))
                            <p class="mt-1 text-xs text-red-500">{{ $errors->updatePassword->first('password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm text-slate-700">{{ __('Confirm Password') }}</label>
                        <input type="password" name="password_confirmation" placeholder="{{ __('Ulangi password baru') }}"
                               class="w-full rounded-full border border-slate-300 px-5 py-3 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                    </div>

                    <div class="pt-2 flex items-center gap-4">
                        <button type="submit"
                                class="rounded-full bg-[#a4c400] px-7 py-2.5 text-sm font-semibold text-white hover:bg-[#8fad00] transition">
                            {{ __('Simpan') }}
                        </button>
                        @if (session('status') === 'password-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition
                               x-init="setTimeout(() => show = false, 2000)"
                               class="text-sm text-green-600">{{ __('Tersimpan.') }}</p>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Right column ────────────────────────────── --}}
        <div class="space-y-6">

            {{-- Two-factor auth --}}
            <div class="rounded-2xl bg-white p-8">
                <h2 class="mb-2 text-lg font-bold text-slate-900">{{ __('Aktifkan Dua Faktor Autentikasi') }}</h2>
                <p class="mb-5 text-sm text-slate-500">
                    {{ __('Tambahkan keamanan ekstra dengan meminta kode verifikasi saat autentikasi') }}
                </p>

                @if ($tfEnabled)
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-green-50 px-3 py-1 text-sm font-medium text-green-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('2FA aktif') }}
                    </div>

                    @if ($showCodes)
                        <div class="mb-4 rounded-xl bg-slate-50 p-4">
                            <p class="mb-3 text-sm text-slate-600">{{ __('Simpan kode pemulihan ini di tempat aman. Setiap kode hanya bisa digunakan sekali.') }}</p>
                            <div class="grid grid-cols-2 gap-2 font-mono text-sm">
                                @foreach ($user->recoveryCodes() as $code)
                                    <span class="rounded-lg bg-white px-2 py-1 text-slate-800">{{ $code }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('two-factor.recovery-codes') }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-full border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                                {{ __('Buat ulang kode') }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('two-factor.disable') }}"
                              data-confirm="{{ __('Akun Anda tidak akan lagi meminta kode saat masuk.') }}"
                              data-confirm-title="{{ __('Nonaktifkan 2FA?') }}"
                              data-confirm-action="{{ __('Ya, Nonaktifkan') }}"
                              data-confirm-type="danger">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded-full bg-red-500 px-5 py-2 text-sm font-semibold text-white hover:bg-red-600 transition">
                                {{ __('Nonaktifkan 2FA') }}
                            </button>
                        </form>
                    </div>

                @elseif ($tfPending)
                    <div class="space-y-4">
                        <p class="text-sm text-slate-600">{{ __('Pindai QR ini dengan Google Authenticator, Authy, atau aplikasi sejenis.') }}</p>

                        <div class="flex justify-center rounded-xl border border-slate-200 p-3">
                            {!! \App\Support\TwoFactorQrCode::svg($user) !!}
                        </div>

                        <p class="break-all rounded-lg bg-slate-50 px-3 py-2 font-mono text-xs text-slate-800">{{ $user->two_factor_secret }}</p>

                        <form method="POST" action="{{ route('two-factor.confirm') }}">
                            @csrf
                            <label class="mb-1.5 block text-sm text-slate-700">{{ __('Kode 6 digit') }}</label>
                            <div class="flex items-center gap-3">
                                <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required
                                       class="w-full rounded-full border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                                <button type="submit"
                                        class="shrink-0 rounded-full bg-[#a4c400] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#8fad00] transition">
                                    {{ __('Konfirmasi') }}
                                </button>
                            </div>
                            @error('code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </form>

                        <form method="POST" action="{{ route('two-factor.disable') }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm text-slate-400 underline hover:text-slate-600">{{ __('Batal') }}</button>
                        </form>
                    </div>

                @else
                    <form method="POST" action="{{ route('two-factor.enable') }}">
                        @csrf
                        <button type="submit"
                                class="rounded-full bg-[#a4c400] px-7 py-2.5 text-sm font-semibold text-white hover:bg-[#8fad00] transition">
                            {{ __('Aktifkan') }}
                        </button>
                    </form>
                @endif
            </div>

            {{-- Delete account --}}
            <div class="rounded-2xl bg-white p-8">
                <h2 class="mb-2 text-lg font-bold text-slate-900">{{ __('Hapus Akun') }}</h2>
                <p class="mb-5 text-sm text-slate-500">
                    {{ __('Setelah anda menghapus akun, maka seluruh data akan otomatis terhapus') }}
                </p>
                <button type="button"
                        x-data=""
                        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                        class="rounded-full bg-red-500 px-7 py-2.5 text-sm font-semibold text-white hover:bg-red-600 transition">
                    {{ __('Hapus') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Delete account modal --}}
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="POST" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-slate-900">{{ __('Hapus akun?') }}</h2>
            <p class="mt-2 text-sm text-slate-600">
                {{ __('Seluruh data akun Anda akan dihapus permanen. Masukkan kata sandi untuk mengonfirmasi.') }}
            </p>

            <div class="mt-5">
                <input type="password" name="password" placeholder="{{ __('Kata sandi') }}"
                       class="w-full rounded-full border border-slate-300 px-5 py-3 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100 transition">
                @if ($errors->userDeletion->has('password'))
                    <p class="mt-1 text-xs text-red-500">{{ $errors->userDeletion->first('password') }}</p>
                @endif
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')"
                        class="rounded-full border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    {{ __('Batal') }}
                </button>
                <button type="submit"
                        class="rounded-full bg-red-500 px-5 py-2 text-sm font-semibold text-white hover:bg-red-600 transition">
                    {{ __('Ya, Hapus Akun') }}
                </button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
