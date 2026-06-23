<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] bg-gradient-to-br from-blue-100 via-slate-100 to-blue-200 flex items-center justify-center py-16 px-4">
        <div class="flex flex-col items-center">
            {{-- Success heading --}}
            <div class="mb-6 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-lime-400">
                    <svg class="h-5 w-5 text-slate-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                <h2 class="text-2xl font-bold text-slate-900">Berhasil membuat sistem kasir!</h2>
            </div>

            {{-- Preview card --}}
            <div class="w-[340px] sm:w-[400px]">
                @php $screenshot = $tenant->screenshotUrl(); @endphp
                @if ($screenshot)
                    <div class="overflow-hidden rounded-2xl shadow-md">
                        <img src="{{ $screenshot }}" alt="{{ $tenant->name }}"
                             class="w-full object-cover object-top">
                    </div>
                @else
                    <div class="h-60 w-full rounded-2xl bg-slate-200/80 shadow-md"></div>
                @endif
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
