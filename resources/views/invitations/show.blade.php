<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __("Terima Undangan — Kasiro") }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.clarity')
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-2xl shadow-md p-8 w-full max-w-md text-center">
        <div class="w-14 h-14 rounded-full bg-indigo-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
            </svg>
        </div>

        <h1 class="text-xl font-bold text-gray-900 mb-1">{{ __("Undangan Karyawan") }}</h1>
        <p data-clarity-mask="true" class="text-sm text-gray-500 mb-6">
            {!! __('Anda diundang bergabung di :tenant sebagai :role.', [
                'tenant' => '<strong class="text-gray-800">'.e($invitation->tenant->name).'</strong>',
                'role'   => '<strong class="text-indigo-700">'.e(ucfirst($invitation->role)).'</strong>',
            ]) !!}
        </p>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                {{ $errors->first('invite') }}
            </div>
        @endif

        @auth
            <form method="POST" action="{{ route('invitations.accept', ['token' => $token]) }}">
                @csrf
                <button type="submit"
                    class="w-full py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition">{{ __("Terima Undangan") }}</button>
            </form>
            <p class="mt-4 text-xs text-gray-400">
                {{ __('Anda login sebagai :email.', ['email' => auth()->user()->email]) }}
                {{ __('Tidak ada Anda?') }} <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();" class="underline">{{ __('Keluar') }}</a>
            </p>
            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>
        @else
            <a href="{{ route('login') }}"
                class="block w-full py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition mb-3">
                Masuk untuk Menerima
            </a>
            <a href="{{ route('register') }}" class="text-sm text-indigo-600 hover:underline">
                Belum punya akun? Daftar dulu
            </a>
        @endauth

        <p class="mt-6 text-xs text-gray-400">
            {{ __('Undangan kedaluwarsa :time.', ['time' => $invitation->expires_at->diffForHumans()]) }}
        </p>
    </div>
</body>
</html>
