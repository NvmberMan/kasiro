<x-guest-layout>
    <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Login') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('Selamat datang kembali!') }}</p>
    </div>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <x-auth-input name="email" type="email" icon="mail"
                      :value="old('email')" placeholder="{{ __('example@mail.com') }}"
                      autocomplete="username" required autofocus />

        <div>
            <x-auth-input name="password" type="password" icon="lock"
                          placeholder="{{ __('Password') }}"
                          autocomplete="current-password" required />

            @if (Route::has('password.request'))
                <div class="mt-2 text-right">
                    <a href="{{ route('password.request') }}"
                       class="text-xs font-medium text-blue-600 hover:text-blue-800">
                        {{ __('Lupa Kata Sandi?') }}
                    </a>
                </div>
            @endif
        </div>

        <x-auth-button class="mt-2">{{ __('Login') }}</x-auth-button>
    </form>

    @include('auth.partials.social-buttons')

    <p class="mt-6 text-center text-sm text-gray-500">
        {{ __('Belum punya akun?') }}
        <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-800">{{ __('Sign up') }}</a>
    </p>
</x-guest-layout>
