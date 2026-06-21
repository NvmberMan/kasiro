<x-guest-layout>
    <div x-data="{ recovery: false }">
        <div class="mb-4 text-sm text-gray-600">
            <template x-if="! recovery">
                <span>{{ __('Masukkan kode dari aplikasi autentikasi Anda untuk melanjutkan.') }}</span>
            </template>
            <template x-if="recovery">
                <span>{{ __('Masukkan salah satu kode pemulihan darurat Anda.') }}</span>
            </template>
        </div>

        <form method="POST" action="{{ route('two-factor.login.store') }}">
            @csrf

            <div x-show="! recovery">
                <x-input-label for="code" :value="__('Kode Autentikasi')" />
                <x-text-input id="code" class="block mt-1 w-full" type="text" name="code"
                              inputmode="numeric" autocomplete="one-time-code" autofocus
                              x-ref="code" />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div x-show="recovery" x-cloak>
                <x-input-label for="recovery_code" :value="__('Kode Pemulihan')" />
                <x-text-input id="recovery_code" class="block mt-1 w-full" type="text"
                              name="recovery_code" autocomplete="one-time-code" />
                <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mt-4">
                <button type="button" x-show="! recovery"
                        x-on:click="recovery = true; $nextTick(() => $refs.code.value = '')"
                        class="underline text-sm text-gray-600 hover:text-gray-900">
                    {{ __('Gunakan kode pemulihan') }}
                </button>
                <button type="button" x-show="recovery" x-cloak
                        x-on:click="recovery = false"
                        class="underline text-sm text-gray-600 hover:text-gray-900">
                    {{ __('Gunakan kode autentikasi') }}
                </button>

                <x-primary-button class="ms-3">
                    {{ __('Verifikasi') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
