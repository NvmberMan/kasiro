<x-guest-layout>
    <div x-data="{ recovery: false }">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900">{{ __('Verifikasi Dua Faktor') }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                <span x-show="! recovery">{{ __('Masukkan kode dari aplikasi autentikasi Anda.') }}</span>
                <span x-show="recovery" x-cloak>{{ __('Masukkan salah satu kode pemulihan darurat Anda.') }}</span>
            </p>
        </div>

        <form method="POST" action="{{ route('two-factor.login.store') }}" class="mt-6 space-y-4">
            @csrf

            <div x-show="! recovery">
                <x-auth-input name="code" type="text" icon="lock"
                              placeholder="{{ __('Kode 6 digit') }}"
                              inputmode="numeric" autocomplete="one-time-code" x-ref="code" autofocus />
            </div>

            <div x-show="recovery" x-cloak>
                <x-auth-input name="recovery_code" type="text" icon="lock"
                              placeholder="{{ __('Kode pemulihan') }}"
                              autocomplete="one-time-code" />
            </div>

            <x-auth-button>{{ __('Verifikasi') }}</x-auth-button>
        </form>

        <div class="mt-5 text-center">
            <button type="button" x-show="! recovery"
                    x-on:click="recovery = true"
                    class="text-sm font-medium text-blue-600 hover:text-blue-800">
                {{ __('Gunakan kode pemulihan') }}
            </button>
            <button type="button" x-show="recovery" x-cloak
                    x-on:click="recovery = false"
                    class="text-sm font-medium text-blue-600 hover:text-blue-800">
                {{ __('Gunakan kode autentikasi') }}
            </button>
        </div>
    </div>
</x-guest-layout>
