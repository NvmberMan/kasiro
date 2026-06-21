<x-guest-layout>
    <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Daftar') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('Buat akun Kasiro baru.') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <x-auth-input name="name" type="text" icon="user"
                      :value="old('name')" placeholder="{{ __('Nama lengkap') }}"
                      autocomplete="name" required autofocus />

        <x-auth-input name="email" type="email" icon="mail"
                      :value="old('email')" placeholder="{{ __('example@mail.com') }}"
                      autocomplete="username" required />

        <x-auth-input name="password" type="password" icon="lock"
                      placeholder="{{ __('Password') }}"
                      autocomplete="new-password" required />

        <x-auth-input name="password_confirmation" type="password" icon="lock"
                      placeholder="{{ __('Konfirmasi password') }}"
                      autocomplete="new-password" required />

        <x-auth-button class="mt-2">{{ __('Daftar') }}</x-auth-button>
    </form>

    @include('auth.partials.social-buttons')

    <p class="mt-6 text-center text-sm text-gray-500">
        {{ __('Sudah punya akun?') }}
        <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-800">{{ __('Login') }}</a>
    </p>
</x-guest-layout>
