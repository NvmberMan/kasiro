@if (config('services.google.client_id'))
    <div class="mt-6">
        <div class="relative">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="bg-white px-2 text-gray-500">{{ __('atau') }}</span>
            </div>
        </div>

        <a href="{{ route('auth.google.redirect') }}"
           class="mt-6 inline-flex w-full items-center justify-center gap-3 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.24 1.4-.96 2.6-2.05 3.4v2.8h3.32C20.7 18.4 21.8 15.5 21.8 12c0-.67-.06-1.32-.17-1.95H12z"/>
                <path fill="#34A853" d="M12 22c2.7 0 4.97-.9 6.62-2.43l-3.32-2.58c-.92.62-2.1.98-3.3.98-2.54 0-4.7-1.72-5.47-4.03H3.1v2.6A10 10 0 0012 22z"/>
                <path fill="#FBBC05" d="M6.53 13.94A6.01 6.01 0 016.2 12c0-.67.12-1.33.33-1.94V7.46H3.1A10 10 0 002 12c0 1.6.39 3.12 1.1 4.54l3.43-2.6z"/>
                <path fill="#4285F4" d="M12 6.03c1.47 0 2.78.5 3.82 1.5l2.86-2.86C16.96 2.99 14.7 2 12 2A10 10 0 003.1 7.46l3.43 2.6C7.3 7.75 9.46 6.03 12 6.03z"/>
            </svg>
            {{ __('Lanjutkan dengan Google') }}
        </a>
    </div>
@endif
