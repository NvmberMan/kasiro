<x-app-layout>
    @php
        $screenshotBase = asset('storage/screenshots/'.$tenant->subdomain.'.jpg');
        $screenshotReady = (bool) $tenant->screenshot_path
            && file_exists(storage_path('app/public/screenshots/'.$tenant->subdomain.'.jpg'));
    @endphp

    {{-- Full-screen overlay: sits above the admin header (z-40) and sidebar (z-30). --}}
    <div class="fixed inset-0 z-50 overflow-y-auto bg-gradient-to-br from-blue-100 via-slate-100 to-blue-200">
        <div class="flex min-h-full flex-col items-center justify-center px-4 py-16">

            {{-- Success heading --}}
            <div class="mb-6 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-lime-400">
                    <svg class="h-5 w-5 text-slate-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                <h2 class="text-2xl font-bold text-slate-900">Berhasil membuat sistem kasir!</h2>
            </div>

            {{-- Preview card — lazy-retries until the background screenshot is ready --}}
            <div class="w-[340px] sm:w-[400px]"
                 x-data="{
                    loaded: false,
                    attempts: 0,
                    src: '{{ $screenshotBase }}?v={{ time() }}',
                    onError() {
                        if (this.attempts++ >= 15) return; // give up after ~30s
                        setTimeout(() => { this.src = '{{ $screenshotBase }}?v=' + Date.now(); }, 2000);
                    }
                 }">
                <div class="relative overflow-hidden rounded-2xl bg-slate-200/80 shadow-md">
                    <img :src="src" alt="{{ $tenant->name }}"
                         x-on:load="loaded = true" x-on:error="loaded = false; onError()"
                         x-show="loaded" x-cloak
                         class="w-full object-cover object-top">
                    <div x-show="!loaded" class="flex h-60 w-full items-center justify-center">
                        <div class="flex flex-col items-center gap-2 text-slate-400">
                            <svg class="h-6 w-6 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z"/>
                            </svg>
                            <span class="text-xs">Menyiapkan pratinjau…</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Subdomain link --}}
            <a href="{{ $tenant->subdomainUrl() }}" target="_blank" rel="noopener"
               class="mt-6 flex w-[340px] sm:w-[400px] items-center justify-center gap-2 rounded-full border-2 border-blue-500 px-6 py-3 text-sm font-semibold text-blue-600 transition hover:bg-blue-50">
                {{ $tenant->subdomain }}.{{ config('tenancy.central_domain') }}
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M9 7h8v8"/>
                </svg>
            </a>

            {{-- Back to studio --}}
            <a href="{{ route('dashboard') }}"
               class="mt-4 inline-flex items-center rounded-full bg-lime-400 px-8 py-3 text-sm font-bold text-slate-900 transition hover:bg-lime-500">
                Kembali ke Studio
            </a>
        </div>
    </div>
</x-app-layout>
