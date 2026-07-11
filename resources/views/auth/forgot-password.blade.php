<x-guest-layout>
    <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Lupa Kata Sandi') }}</h1>
        <p class="mt-1 text-sm text-gray-500">
            {{ __('Masukkan email Anda, kami akan mengirim tautan untuk mengatur ulang kata sandi.') }}
        </p>
    </div>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf

        <x-auth-input name="email" type="email" icon="mail"
                      :value="old('email')" placeholder="{{ __('example@mail.com') }}"
                      autocomplete="username" required autofocus />

        <x-auth-button>{{ __('Kirim Tautan Reset') }}</x-auth-button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-800">{{ __('Kembali ke Login') }}</a>
    </p>
</x-guest-layout>
