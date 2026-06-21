<x-guest-layout>
    <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Verifikasi Email') }}</h1>
        <p class="mt-1 text-sm text-gray-500">
            {{ __('Terima kasih sudah mendaftar! Sebelum mulai, silakan verifikasi email Anda lewat tautan yang baru kami kirim. Jika belum menerimanya, kami bisa kirim ulang.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-4 rounded-lg bg-green-50 px-4 py-2 text-center text-sm font-medium text-green-700">
            {{ __('Tautan verifikasi baru telah dikirim ke email Anda.') }}
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
        @csrf
        <x-auth-button>{{ __('Kirim Ulang Email Verifikasi') }}</x-auth-button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm font-medium text-gray-500 hover:text-gray-700">
            {{ __('Keluar') }}
        </button>
    </form>
</x-guest-layout>
