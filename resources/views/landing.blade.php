<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kasiro — POS Bermerek untuk UMKM</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Wix+Madefor+Text:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak]{display:none !important}
        /* Placeholder gambar (pola kotak-kotak transparan) */
        .ph{
            background-color:#f3f4f6;
            background-image:
                linear-gradient(45deg,#e5e7eb 25%,transparent 25%),
                linear-gradient(-45deg,#e5e7eb 25%,transparent 25%),
                linear-gradient(45deg,transparent 75%,#e5e7eb 75%),
                linear-gradient(-45deg,transparent 75%,#e5e7eb 75%);
            background-size:22px 22px;
            background-position:0 0,0 11px,11px -11px,-11px 0;
        }
    </style>
</head>
<body class="font-sans antialiased bg-white text-slate-900">

{{-- ===== Navbar ===== --}}
<header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-slate-100">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 flex items-center justify-between h-16">
        <a href="/" class="flex items-center">
            <img src="{{ asset('images/kasiro-logo-black.png') }}" alt="Kasiro" class="h-6 w-auto">
        </a>

        <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
            <a href="#beranda" class="hover:text-slate-900 transition">Beranda</a>
            <a href="#tentang" class="hover:text-slate-900 transition">Tentang</a>
            <a href="#bantuan" class="hover:text-slate-900 transition">Bantuan</a>
        </nav>

        <div class="flex items-center gap-2">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="rounded-full border border-blue-600 px-5 py-1.5 text-sm font-semibold text-blue-600 hover:bg-blue-50 transition">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="rounded-full border border-blue-600 px-5 py-1.5 text-sm font-semibold text-blue-600 hover:bg-blue-50 transition">
                    Login
                </a>
            @endauth
        </div>
    </div>
</header>

{{-- ===== Hero ===== --}}
<section id="beranda" class="relative overflow-hidden bg-gradient-to-b from-blue-50 to-white">
    <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-blue-400/30"></div>
    <div class="pointer-events-none absolute right-1/3 top-20 h-40 w-40 rounded-full bg-blue-500/20"></div>

    <div class="relative mx-auto max-w-6xl px-4 sm:px-6 py-16 sm:py-20 grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight tracking-tight text-slate-900">
                Transaksi Mudah,<br>Usaha Terarah
            </h1>
            <p class="mt-5 max-w-md text-sm sm:text-base text-slate-500 leading-relaxed">
                Partner digital UMKM dalam mengarahkan usaha menuju dunia yang lebih modern
                melalui pembuatan sistem kasir yang sesuai dengan identitas unik setiap bisnis.
            </p>
            <a href="{{ auth()->check() ? route('tenants.choose') : route('register') }}"
               class="mt-7 inline-flex items-center gap-2 rounded-full bg-lime-400 px-6 py-2.5 text-sm font-semibold text-slate-900 hover:bg-lime-500 transition">
                Buat Sekarang
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>

        <div class="relative">
            <div class="ph aspect-[4/3] w-full rounded-2xl ring-1 ring-slate-200"></div>
        </div>
    </div>
</section>

{{-- ===== Template Kasir ===== --}}
<section id="tentang" class="mx-auto max-w-6xl px-4 sm:px-6 py-14">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-xl sm:text-2xl font-bold text-slate-900">Template Kasir</h2>
        <a href="{{ route('tenants.showcase') }}"
           class="inline-flex items-center gap-1.5 rounded-full bg-blue-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition">
            Lainnya
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        @for ($i = 0; $i < 3; $i++)
            @php $tpl = $templates[$i] ?? null; @endphp
            <div>
                <div class="ph h-44 w-full rounded-xl ring-1 ring-slate-200"></div>
                <p class="mt-3 text-sm font-medium text-slate-700">{{ $tpl?->name ?? 'Nama Template' }}</p>
            </div>
        @endfor
    </div>
</section>

{{-- ===== 3 Hal yang membuat KASIRO berbeda ===== --}}
<section class="bg-[#0c2461] py-14">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <h2 class="text-center text-xl sm:text-2xl font-bold text-white mb-10">3 Hal yang membuat KASIRO berbeda</h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            @php
                $diffs = [
                    ['01', 'Cepat & Mudah', 'Buat dan atur sistem kasir dalam hitungan menit tanpa keahlian teknis.', 'M13 10V3L4 14h7v7l9-11h-7z'],
                    ['02', 'Sesuai Identitas', 'Sesuaikan aplikasi kasir dengan warna, logo, dan nama toko sendiri.', 'M9.5 14.5L3 21M14 4l6 6M12.5 6.5l5 5L8 21H3v-5z'],
                    ['03', 'Selalu Terhubung', 'Akses laporan dan kelola toko dari mana saja, kapan saja.', 'M13.8 10.2a4 4 0 010 5.6l-2.8 2.8a4 4 0 01-5.6-5.6l1.4-1.4M10.2 13.8a4 4 0 010-5.6l2.8-2.8a4 4 0 015.6 5.6l-1.4 1.4'],
                ];
            @endphp
            @foreach ($diffs as [$num, $title, $text, $icon])
                <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-lime-400 text-slate-900">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        </span>
                        <span class="text-2xl font-extrabold text-white/30">{{ $num }}</span>
                    </div>
                    <h3 class="font-semibold text-white mb-1">{{ $title }}</h3>
                    <p class="text-sm text-blue-100/70 leading-relaxed">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== Apa saja yang ada dalam sistem Kasir? ===== --}}
<section class="relative overflow-hidden py-16">
    <div class="pointer-events-none absolute -left-24 top-10 h-72 w-72 rounded-full bg-blue-400/20"></div>
    <div class="pointer-events-none absolute -right-20 bottom-0 h-64 w-64 rounded-full bg-blue-500/15"></div>

    <div class="relative mx-auto max-w-6xl px-4 sm:px-6">
        <h2 class="text-center text-xl sm:text-2xl font-bold text-slate-900 mb-10">Apa saja yang ada dalam sistem Kasir?</h2>

        <div class="grid lg:grid-cols-2 gap-6 items-stretch">
            <div class="ph min-h-[18rem] rounded-2xl ring-1 ring-slate-200"></div>

            <div class="relative rounded-2xl bg-lime-400 p-8 flex flex-col">
                <h3 class="text-2xl font-bold text-slate-900">Title 1</h3>
                <p class="mt-2 text-sm text-slate-800/70 leading-relaxed max-w-sm">
                    Placeholder deskripsi fitur. Bagian ini akan menjelaskan salah satu fitur
                    utama sistem kasir Kasiro.
                </p>
                <button type="button"
                        class="mt-auto self-end flex h-11 w-11 items-center justify-center rounded-full bg-slate-900/90 text-white hover:bg-slate-900 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
                </button>
            </div>
        </div>
    </div>
</section>

{{-- ===== Pertanyaan Umum (FAQ) ===== --}}
<section id="bantuan" class="bg-blue-50 py-16">
    <div class="mx-auto max-w-5xl px-4 sm:px-6">
        <div class="flex items-center justify-center gap-2 mb-10">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-white text-sm font-bold">?</span>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900">Pertanyaan Umum</h2>
        </div>

        @php
            $faqs = [
                ['Apa itu Kasiro?', 'Kasiro adalah platform pembuatan sistem kasir digital bermerek untuk UMKM.'],
                ['Apakah gratis?', 'Anda bisa mulai gratis tanpa kartu kredit dan langsung membuat toko.'],
                ['Apakah bisa custom brand?', 'Ya, Anda bisa menyesuaikan warna, logo, dan nama toko sendiri.'],
                ['Bagaimana mengundang karyawan?', 'Cukup bagikan link undangan; atur peran dan cabut akses kapan saja.'],
                ['Apakah ada laporan penjualan?', 'Tersedia laporan harian, produk terlaris, dan ringkasan bulanan.'],
                ['Apakah data saya aman?', 'Setiap toko terisolasi pada subdomain khusus dengan akses berbasis peran.'],
            ];
        @endphp
        <div class="grid md:grid-cols-2 gap-4">
            @foreach ($faqs as [$q, $a])
                <div x-data="{ open: false }" class="rounded-2xl bg-white ring-1 ring-slate-100">
                    <button type="button" x-on:click="open = !open"
                            class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left">
                        <span class="text-sm font-medium text-slate-800">{{ $q }}</span>
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-lime-400 text-slate-900 transition" :class="open && 'rotate-180'">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                        </span>
                    </button>
                    <div x-show="open" x-cloak x-transition
                         class="px-5 pb-4 text-sm text-slate-500 leading-relaxed">
                        {{ $a }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== CTA ===== --}}
<section class="bg-blue-600 py-16 text-center">
    <div class="mx-auto max-w-xl px-4">
        <h2 class="text-2xl sm:text-3xl font-bold text-white">Kami Mendengar Anda!</h2>
        <p class="mt-3 text-sm text-blue-100/80 leading-relaxed">
            Punya masukan atau pertanyaan seputar sistem kasir Kasiro? Sampaikan kepada kami.
        </p>
        <a href="{{ auth()->check() ? route('tenants.choose') : route('register') }}"
           class="mt-6 inline-block rounded-full bg-lime-400 px-8 py-2.5 text-sm font-semibold text-slate-900 hover:bg-lime-500 transition">
            Mulai Sekarang
        </a>
    </div>
</section>

{{-- ===== Footer ===== --}}
<footer class="bg-[#0c2461] py-6">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="mailto:halo@kasiro.my.id" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition" aria-label="Email">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M3 8v8a2 2 0 002 2h14a2 2 0 002-2V8M3 8a2 2 0 012-2h14a2 2 0 012 2"/></svg>
            </a>
            <a href="tel:+62" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition" aria-label="Telepon">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.3a1 1 0 01.95.68l1 3a1 1 0 01-.27 1.05L8.2 9.2a12 12 0 006.6 6.6l1.47-1.48a1 1 0 011.05-.27l3 1a1 1 0 01.68.95V19a2 2 0 01-2 2A16 16 0 013 5z"/></svg>
            </a>
        </div>
        <p class="text-xs text-blue-100/60">&copy; {{ date('Y') }} kasiro.my.id</p>
    </div>
</footer>

</body>
</html>
