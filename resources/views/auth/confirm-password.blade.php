<x-guest-layout>
    <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Konfirmasi Password') }}</h1>
        <p class="mt-1 text-sm text-gray-500">
            {{ __('Ini area aman. Konfirmasi password Anda sebelum melanjutkan.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
        @csrf

        <x-auth-input name="password" type="password" icon="lock"
                      placeholder="{{ __('Password') }}"
                      autocomplete="current-password" required autofocus />

        <x-auth-button>{{ __('Konfirmasi') }}</x-auth-button>
    </form>
</x-guest-layout>
