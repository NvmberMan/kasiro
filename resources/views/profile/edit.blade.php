<x-app-layout>
    @php
        $user = auth()->user();
        $hasPassword = $user->password !== null;
        $tfEnabled = $user->hasTwoFactorEnabled();
        $tfPending = $user->two_factor_secret && ! $tfEnabled;
        $showCodes = in_array(session('status'), ['two-factor-enabled', 'recovery-codes-generated'], true);
    @endphp

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Profil Anda</h1>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex items-center gap-2 rounded-full border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                Logout
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_360px]">

        {{-- ── Left column ─────────────────────────────── --}}
        <div class="space-y-6">

            {{-- Personal info --}}
            <div class="rounded-2xl bg-white p-8">
                <h2 class="mb-6 text-lg font-bold text-slate-900">Informasi personal</h2>

                {{-- Avatar upload --}}
                <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data"
                      x-data="{ preview: @js($user->avatar), changed: false,
                                 pick(e) {
                                     const f = e.target.files[0];
                                     if (!f) return;
                                     this.preview = URL.createObjectURL(f);
                                     this.changed = true;
                                 } }"
                      class="mb-6">
                    @csrf
                    <div class="flex items-center gap-4">
                        <div class="relative shrink-0">
                            {{-- Avatar circle: preview jika ada, fallback ikon --}}
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

                            {{-- Tombol pensil --}}
                            <label for="avatar-input"
                                   class="absolute -bottom-0.5 -right-0.5 flex h-5 w-5 cursor-pointer items-center justify-center rounded-full bg-slate-700 text-white hover:bg-slate-900 transition">
                                <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-1.414.94l-3.414.94.94-3.414A4 4 0 019 13z"/>
                                </svg>
                            </label>
                            <input id="avatar-input" name="avatar" type="file" accept="image/*" class="hidden"
                                   x-on:change="pick($event)">
                        </div>

                        <div>
                            <p class="text-sm font-medium text-slate-700">Foto Profil</p>
                            <p class="text-xs text-slate-400" x-show="!changed">JPG, PNG · maks 2 MB</p>
                            <p class="text-xs text-[#a4c400] font-medium" x-show="changed" x-cloak>Foto baru dipilih — klik Simpan</p>
                            <button type="submit" x-show="changed" x-cloak
                                    class="mt-2 rounded-full bg-[#a4c400] px-5 py-1.5 text-xs font-semibold text-white hover:bg-[#8fad00] transition">
                                Simpan Foto
                            </button>
                            @if ($errors->has('avatar'))
                                <p class="mt-1 text-xs text-red-500">{{ $errors->first('avatar') }}</p>
                            @endif
                            @if (session('status') === 'avatar-updated')
                                <p x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                                   class="mt-1 text-xs text-green-600 font-medium">✓ Foto berhasil diperbarui</p>
                            @endif
                        </div>
                    </div>
                </form>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('patch')

                    <div>
                        <label class="mb-1.5 block text-sm text-slate-700">Username</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                               placeholder="Nama lengkap"
                               class="w-full rounded-full border border-slate-300 px-5 py-3 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                        @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                               placeholder="contoh@email.com"
                               class="w-full rounded-full border border-slate-300 px-5 py-3 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                        @error('email')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2 flex items-center gap-4">
                        <button type="submit"
                                class="rounded-full bg-[#a4c400] px-7 py-2.5 text-sm font-semibold text-white hover:bg-[#8fad00] transition">
                            Simpan
                        </button>
                        @if (session('status') === 'profile-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition
                               x-init="setTimeout(() => show = false, 2000)"
                               class="text-sm text-green-600">Tersimpan.</p>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Password --}}
            <div class="rounded-2xl bg-white p-8">
                <h2 class="mb-6 text-lg font-bold text-slate-900">Update Password Akun Anda</h2>

                <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    @method('put')

                    @if ($hasPassword)
                        <div>
                            <label class="mb-1.5 block text-sm text-slate-700">Current Password</label>
                            <input type="password" name="current_password" placeholder="Password saat ini"
                                   class="w-full rounded-full border border-slate-300 px-5 py-3 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                            @if ($errors->updatePassword->has('current_password'))
                                <p class="mt-1 text-xs text-red-500">{{ $errors->updatePassword->first('current_password') }}</p>
                            @endif
                        </div>
                    @endif

                    <div>
                        <label class="mb-1.5 block text-sm text-slate-700">New Password</label>
                        <input type="password" name="password" placeholder="Password baru"
                               class="w-full rounded-full border border-slate-300 px-5 py-3 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                        @if ($errors->updatePassword->has('password'))
                            <p class="mt-1 text-xs text-red-500">{{ $errors->updatePassword->first('password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm text-slate-700">Confirm Password</label>
                        <input type="password" name="password_confirmation" placeholder="Ulangi password baru"
                               class="w-full rounded-full border border-slate-300 px-5 py-3 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                    </div>

                    <div class="pt-2 flex items-center gap-4">
                        <button type="submit"
                                class="rounded-full bg-[#a4c400] px-7 py-2.5 text-sm font-semibold text-white hover:bg-[#8fad00] transition">
                            Simpan
                        </button>
                        @if (session('status') === 'password-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition
                               x-init="setTimeout(() => show = false, 2000)"
                               class="text-sm text-green-600">Tersimpan.</p>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Right column ────────────────────────────── --}}
        <div class="space-y-6">

            {{-- Two-factor auth --}}
            <div class="rounded-2xl bg-white p-8">
                <h2 class="mb-2 text-lg font-bold text-slate-900">Aktifkan Dua Faktor Autentikasi</h2>
                <p class="mb-5 text-sm text-slate-500">
                    Tambahkan keamanan ekstra dengan meminta kode verifikasi saat autentikasi
                </p>

                @if ($tfEnabled)
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-green-50 px-3 py-1 text-sm font-medium text-green-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        2FA aktif
                    </div>

                    @if ($showCodes)
                        <div class="mb-4 rounded-xl bg-slate-50 p-4">
                            <p class="mb-3 text-sm text-slate-600">Simpan kode pemulihan ini di tempat aman. Setiap kode hanya bisa digunakan sekali.</p>
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
                                Buat ulang kode
                            </button>
                        </form>
                        <form method="POST" action="{{ route('two-factor.disable') }}"
                              data-confirm="Akun Anda tidak akan lagi meminta kode saat masuk."
                              data-confirm-title="Nonaktifkan 2FA?"
                              data-confirm-action="Ya, Nonaktifkan"
                              data-confirm-type="danger">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded-full bg-red-500 px-5 py-2 text-sm font-semibold text-white hover:bg-red-600 transition">
                                Nonaktifkan 2FA
                            </button>
                        </form>
                    </div>

                @elseif ($tfPending)
                    <div class="space-y-4">
                        <p class="text-sm text-slate-600">Pindai QR ini dengan Google Authenticator, Authy, atau aplikasi sejenis.</p>

                        <div class="flex justify-center rounded-xl border border-slate-200 p-3">
                            {!! \App\Support\TwoFactorQrCode::svg($user) !!}
                        </div>

                        <p class="break-all rounded-lg bg-slate-50 px-3 py-2 font-mono text-xs text-slate-800">{{ $user->two_factor_secret }}</p>

                        <form method="POST" action="{{ route('two-factor.confirm') }}">
                            @csrf
                            <label class="mb-1.5 block text-sm text-slate-700">Kode 6 digit</label>
                            <div class="flex items-center gap-3">
                                <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required
                                       class="w-full rounded-full border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                                <button type="submit"
                                        class="shrink-0 rounded-full bg-[#a4c400] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#8fad00] transition">
                                    Konfirmasi
                                </button>
                            </div>
                            @error('code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </form>

                        <form method="POST" action="{{ route('two-factor.disable') }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm text-slate-400 underline hover:text-slate-600">Batal</button>
                        </form>
                    </div>

                @else
                    <form method="POST" action="{{ route('two-factor.enable') }}">
                        @csrf
                        <button type="submit"
                                class="rounded-full bg-[#a4c400] px-7 py-2.5 text-sm font-semibold text-white hover:bg-[#8fad00] transition">
                            Aktifkan
                        </button>
                    </form>
                @endif
            </div>

            {{-- Delete account --}}
            <div class="rounded-2xl bg-white p-8">
                <h2 class="mb-2 text-lg font-bold text-slate-900">Hapus Akun</h2>
                <p class="mb-5 text-sm text-slate-500">
                    Setelah anda menghapus akun, maka seluruh data akan otomatis terhapus
                </p>
                <button type="button"
                        x-data=""
                        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                        class="rounded-full bg-red-500 px-7 py-2.5 text-sm font-semibold text-white hover:bg-red-600 transition">
                    Hapus
                </button>
            </div>
        </div>
    </div>

    {{-- Delete account modal --}}
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="POST" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-slate-900">Hapus akun?</h2>
            <p class="mt-2 text-sm text-slate-600">
                Seluruh data akun Anda akan dihapus permanen. Masukkan kata sandi untuk mengonfirmasi.
            </p>

            <div class="mt-5">
                <input type="password" name="password" placeholder="Kata sandi"
                       class="w-full rounded-full border border-slate-300 px-5 py-3 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100 transition">
                @if ($errors->userDeletion->has('password'))
                    <p class="mt-1 text-xs text-red-500">{{ $errors->userDeletion->first('password') }}</p>
                @endif
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')"
                        class="rounded-full border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit"
                        class="rounded-full bg-red-500 px-5 py-2 text-sm font-semibold text-white hover:bg-red-600 transition">
                    Ya, Hapus Akun
                </button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
