<x-guest-layout>
    <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Lengkapi Akun Anda') }}</h1>
        <p class="mt-1 text-sm text-gray-500">
            {{ __('Anda masuk lewat Google. Buat kata sandi agar bisa juga masuk dengan email dan menjaga akun tetap aman.') }}
        </p>
    </div>

    <div class="mt-6 flex items-center gap-3 rounded-2xl bg-gray-50 p-3">
        @if ($user->avatar)
            <img data-clarity-mask="true" src="{{ $user->avatar }}" alt="" class="h-10 w-10 rounded-full object-cover ring-1 ring-gray-200">
        @else
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-lime-100 text-sm font-bold text-lime-700">
                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
            </span>
        @endif
        <div class="min-w-0">
            <p data-clarity-mask="true" class="truncate text-sm font-semibold text-gray-800">{{ $user->name }}</p>
            <p data-clarity-mask="true" class="truncate text-xs text-gray-500">{{ $user->email }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('profile.complete.store') }}" class="mt-6 space-y-4">
        @csrf

        <x-auth-input name="name" type="text" icon="user"
                      :value="old('name', $user->name)" placeholder="{{ __('Nama lengkap') }}"
                      autocomplete="name" required autofocus />

        <x-auth-input name="password" type="password" icon="lock"
                      placeholder="{{ __('Buat kata sandi') }}"
                      autocomplete="new-password" required />

        <x-auth-input name="password_confirmation" type="password" icon="lock"
                      placeholder="{{ __('Konfirmasi kata sandi') }}"
                      autocomplete="new-password" required />

        <x-auth-button class="mt-2">{{ __('Simpan & Lanjutkan') }}</x-auth-button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">
        @csrf
        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">{{ __('Keluar') }}</button>
    </form>
</x-guest-layout>
