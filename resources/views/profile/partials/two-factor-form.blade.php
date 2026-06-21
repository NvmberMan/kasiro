@php
    $user = $user ?? auth()->user();
    $enabled = $user->hasTwoFactorEnabled();
    $pending = $user->two_factor_secret && ! $enabled;
    $showCodes = in_array(session('status'), ['two-factor-enabled', 'recovery-codes-generated'], true);
@endphp

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Autentikasi Dua Faktor') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Tambahkan keamanan ekstra dengan meminta kode dari aplikasi autentikasi saat masuk.') }}
        </p>
    </header>

    {{-- ON: enabled & confirmed --}}
    @if ($enabled)
        <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-green-50 px-3 py-1 text-sm font-medium text-green-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            {{ __('2FA aktif') }}
        </div>

        @if ($showCodes)
            <div class="mt-4 rounded-md bg-gray-50 p-4">
                <p class="text-sm text-gray-700">
                    {{ __('Simpan kode pemulihan ini di tempat aman. Setiap kode hanya dapat digunakan sekali jika Anda kehilangan akses ke aplikasi autentikasi.') }}
                </p>
                <div class="mt-3 grid grid-cols-2 gap-2 font-mono text-sm">
                    @foreach ($user->recoveryCodes() as $code)
                        <span class="rounded bg-white px-2 py-1 text-gray-800">{{ $code }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-4 flex items-center gap-3">
            <form method="POST" action="{{ route('two-factor.recovery-codes') }}">
                @csrf
                <x-secondary-button>{{ __('Buat ulang kode pemulihan') }}</x-secondary-button>
            </form>

            <form method="POST" action="{{ route('two-factor.disable') }}"
                  data-confirm="{{ __('Akun Anda tidak akan lagi meminta kode saat masuk.') }}"
                  data-confirm-title="{{ __('Nonaktifkan 2FA?') }}"
                  data-confirm-action="{{ __('Ya, Nonaktifkan') }}"
                  data-confirm-type="danger">
                @csrf
                @method('DELETE')
                <x-danger-button type="submit">{{ __('Nonaktifkan 2FA') }}</x-danger-button>
            </form>
        </div>

    {{-- PENDING: secret generated, awaiting confirmation --}}
    @elseif ($pending)
        <div class="mt-4 grid gap-6 sm:grid-cols-[auto,1fr] sm:items-start">
            <div class="rounded-lg border border-gray-200 p-3">
                {!! \App\Support\TwoFactorQrCode::svg($user) !!}
            </div>

            <div>
                <p class="text-sm text-gray-700">
                    {{ __('Pindai QR ini dengan Google Authenticator, Authy, atau aplikasi sejenis. Atau masukkan kunci ini secara manual:') }}
                </p>
                <p class="mt-2 break-all rounded bg-gray-50 px-2 py-1 font-mono text-sm text-gray-800">
                    {{ $user->two_factor_secret }}
                </p>

                <form method="POST" action="{{ route('two-factor.confirm') }}" class="mt-4">
                    @csrf
                    <x-input-label for="code" :value="__('Masukkan kode 6 digit untuk mengonfirmasi')" />
                    <div class="mt-1 flex items-center gap-3">
                        <x-text-input id="code" class="block w-40" type="text" name="code"
                                      inputmode="numeric" autocomplete="one-time-code" required />
                        <x-primary-button>{{ __('Konfirmasi') }}</x-primary-button>
                    </div>
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </form>

                <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-3">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-gray-500 underline hover:text-gray-700">
                        {{ __('Batal') }}
                    </button>
                </form>
            </div>
        </div>

    {{-- OFF --}}
    @else
        <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-4">
            @csrf
            <x-primary-button>{{ __('Aktifkan 2FA') }}</x-primary-button>
        </form>
    @endif
</section>
