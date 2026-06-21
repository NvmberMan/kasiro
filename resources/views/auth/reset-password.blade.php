<x-guest-layout>
    <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Atur Ulang Password') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('Buat password baru untuk akun Anda.') }}</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-auth-input name="email" type="email" icon="mail"
                      :value="old('email', $request->email)" placeholder="{{ __('example@mail.com') }}"
                      autocomplete="username" required autofocus />

        <x-auth-input name="password" type="password" icon="lock"
                      placeholder="{{ __('Password baru') }}"
                      autocomplete="new-password" required />

        <x-auth-input name="password_confirmation" type="password" icon="lock"
                      placeholder="{{ __('Konfirmasi password') }}"
                      autocomplete="new-password" required />

        <x-auth-button>{{ __('Atur Ulang Password') }}</x-auth-button>
    </form>
</x-guest-layout>
