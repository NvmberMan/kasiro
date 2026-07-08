<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Kasiro | POS Bermerek untuk UMKM') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/kasiro-logo.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/kasiro-logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Wix+Madefor+Text:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
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

        <div class="flex gap-[50px]">
            <a href="/" class="flex items-center">
                <img src="{{ asset('images/kasiro-logo-black.png') }}" alt="Kasiro" class="h-5 w-auto">
            </a>
            
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                <a href="#tentang" class="scroll-nav-link hover:text-slate-900 transition">{{ __('Tentang') }}</a>
                <a href="#bantuan" class="scroll-nav-link hover:text-slate-900 transition">{{ __('Bantuan') }}</a>
            </nav>
        </div>

        <div class="flex items-center gap-2">
            <x-language-switcher align="right" />
            @auth
                <a href="{{ route('dashboard') }}"
                   class="rounded-full border border-blue-600 px-5 py-1.5 text-sm font-semibold text-blue-600 hover:bg-blue-50 transition">
                    {{ __('Dashboard') }}
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="rounded-full border border-blue-600 px-5 py-1.5 text-sm font-semibold text-blue-600 hover:bg-blue-50 transition">
                    {{ __('Login') }}
                </a>
                <a href="{{ route('register') }}"
                   class="rounded-full bg-blue-600 px-5 py-1.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
                    {{ __('Daftar') }}
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
            <h1 class="text-3xl sm:text-4xl font-bold leading-tight tracking-tight text-slate-900">
                {!! __('Transaksi Mudah,<br>Usaha Terarah') !!}
            </h1>
            <p class="mt-5 max-w-md text-sm sm:text-base text-slate-500 leading-relaxed">
                {{ __('Partner digital UMKM dalam mengarahkan usaha menuju dunia yang lebih modern melalui pembuatan sistem kasir yang sesuai dengan identitas unik setiap bisnis.') }}
            </p>
            <a href="{{ auth()->check() ? route('tenants.choose') : route('register') }}"
               class="mt-7 inline-flex items-center gap-2 rounded-full bg-lime-400 px-6 py-2.5 text-sm font-semibold text-slate-900 hover:bg-lime-500 transition">
                {{ __('Buat Sekarang') }}
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>

        <div class="relative">
            <img src="{{ asset('images/hero.png') }}"
            {{-- <div class="ph aspect-[3/2] w-full rounded-2xl ring-1 ring-slate-200"></div> --}}
        </div>
    </div>
</section>

{{-- ===== Template Kasir ===== --}}
<section id="template-kasir" class="mx-auto max-w-6xl px-4 sm:px-6 py-14 scroll-mt-16">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-xl sm:text-2xl font-bold text-slate-900">{{ __('Template Kasir') }}</h2>
        <a href="{{ auth()->check() ? route('tenants.create.template') : route('login') }}"
           class="inline-flex items-center gap-1.5 rounded-full bg-blue-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition">
            {{ __('Lainnya') }}
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        @forelse ($templates->take(3) as $i => $tpl)
            @php $thumb = $tpl->screenshotUrl() ?? asset('images/photo' . ($i + 1) . '.png'); @endphp
            <a href="{{ auth()->check() ? route('tenants.create.template', ['template' => $tpl->slug]) : route('login') }}" class="group block">
                <div class="relative aspect-video w-full overflow-hidden rounded-xl ring-1 ring-slate-200 transition group-hover:ring-blue-400 group-hover:shadow-lg">
                    <img class="ph h-full w-full object-cover object-top transition duration-300 group-hover:scale-105"
                         src="{{ $thumb }}"
                         alt="{{ $tpl->name }}">
                    <div class="absolute inset-0 flex items-center justify-center bg-blue-600/60 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                        <span class="rounded-full bg-white px-5 py-2 text-sm font-semibold text-blue-600 shadow">
                            {{ __('Pakai Template') }}
                        </span>
                    </div>
                </div>
                <p class="mt-3 text-sm font-medium text-slate-700 transition group-hover:text-blue-600">{{ $tpl->name }}</p>
            </a>
        @empty
            <p class="col-span-full text-center text-slate-400 py-8">{{ __('Belum ada template tersedia.') }}</p>
        @endforelse
    </div>
</section>

{{-- ===== 3 Hal yang membuat KASIRO berbeda ===== --}}
<section id="tentang" class="bg-[#0c2461] py-16 scroll-mt-16">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <h2 class="text-center text-2xl sm:text-3xl font-bold text-white mb-12">{{ __('3 Hal yang membuat KASIRO berbeda') }}</h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            @php
                $diffs = [
                    ['01', 'Mudah dan cepat dipahami tanpa memerlukan keahlian tertentu', 'images/diff-icon-1.png'],
                    ['02', 'Personalisasi aplikasi kasir sesuai selera dan kebutuhanmu',  'images/diff-icon-2.png'],
                    ['03', 'Membuat link khusus untuk sistem kasirmu',                    'images/diff-icon-3.png'],
                ];
            @endphp
            @foreach ($diffs as [$num, $text, $icon])
                <div class="flex items-center gap-5 rounded-2xl border border-lime-400 px-6 py-7 transition-colors duration-200 hover:bg-[#1e3a8a]">
                    <img src="{{ asset($icon) }}" alt="" class="shrink-0 h-16 w-16 object-contain">
                    <div>
                        <p class="text-xs font-semibold text-lime-400 mb-1">{{ $num }}</p>
                        <p class="text-sm text-lime-400 leading-snug">{{ __($text) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== Apa saja yang ada dalam sistem Kasir? ===== --}}
<section class="relative overflow-hidden bg-slate-100 py-16">

    {{-- Lingkaran biru dekoratif --}}
    <div class="pointer-events-none absolute -right-28 -top-28 h-72 w-72 rounded-full bg-blue-500"></div>
    <div class="pointer-events-none absolute -left-28 -bottom-20 h-80 w-80 rounded-full bg-blue-500"></div>

    @php
        $features = [
            [
                'title' => 'Transaksi Penjualan',
                'desc'  => 'Proses transaksi dengan cepat — pilih produk, hitung total otomatis, dan terima pembayaran tunai maupun QRIS.',
                'img'   => 'images/feature-transaksi.png',
            ],
            [
                'title' => 'Katalog Produk',
                'desc'  => 'Tambah dan kelola produk beserta foto, harga, dan kategorinya dalam hitungan detik.',
                'img'   => 'images/feature-produk.png',
            ],
            [
                'title' => 'Laporan & Analitik',
                'desc'  => 'Pantau omzet harian, produk terlaris, dan tren penjualan bulanan dari satu dasbor.',
                'img'   => 'images/feature-laporan.png',
            ],
            [
                'title' => 'Manajemen Karyawan',
                'desc'  => 'Buat akun kasir terpisah dengan hak akses berbeda dan lacak aktivitas tiap karyawan.',
                'img'   => 'images/feature-karyawan.png',
            ],
        ];
    @endphp

    <div class="relative mx-auto max-w-6xl px-4 sm:px-6">
        <h2 class="text-center text-xl sm:text-2xl font-bold text-slate-900 mb-10">{{ __('Apa saja yang ada dalam sistem Kasir?') }}</h2>

        <div class="relative">
            {{-- Tombol navigasi --}}
            <button class="feature-prev absolute left-0 top-1/2 -translate-y-1/2 -translate-x-5 sm:-translate-x-10 z-20 flex h-11 w-11 items-center justify-center text-slate-500 transition hover:text-slate-900">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button class="feature-next absolute right-0 top-1/2 -translate-y-1/2 translate-x-5 sm:translate-x-10 z-20 flex h-11 w-11 items-center justify-center text-slate-500 transition hover:text-slate-900">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>

            <div class="swiper feature-swiper">
                <div class="swiper-wrapper items-stretch">
                    @foreach ($features as $f)
                    <div class="swiper-slide">
                        <div class="relative flex items-stretch">
                            {{-- Gambar --}}
                            <div class="w-[62%] rounded-l-2xl overflow-hidden">
                                <img src="{{ asset($f['img']) }}" alt="{{ $f['title'] }}" class="ph w-full h-auto block select-none">
                            </div>
                            {{-- Lime card --}}
                            <div class="absolute right-0 top-0 bottom-0 w-[38%] rounded-r-2xl bg-lime-400 p-8 flex flex-col justify-center">
                                <h3 class="text-2xl font-bold text-slate-900">{{ __($f['title']) }}</h3>
                                <p class="mt-3 text-sm text-slate-700/80 leading-relaxed">{{ __($f['desc']) }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Pagination --}}
            <div class="feature-pagination flex justify-center mt-6"></div>
        </div>
    </div>
</section>

{{-- ===== Pertanyaan Umum (FAQ) ===== --}}
<section id="bantuan" class="bg-blue-50 py-16 scroll-mt-16">
    <div class="mx-auto max-w-5xl px-4 sm:px-6">
        <div class="flex items-center justify-center gap-2 mb-10">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-white text-sm font-bold">?</span>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900">{{ __('Pertanyaan Umum') }}</h2>
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
                        <span class="text-sm font-medium text-slate-800">{{ __($q) }}</span>
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-lime-400 text-slate-900 transition" :class="open && 'rotate-180'">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                        </span>
                    </button>
                    <div x-show="open" x-cloak x-transition
                         class="px-5 pb-4 text-sm text-slate-500 leading-relaxed">
                        {{ __($a) }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== CTA ===== --}}
<section class="bg-blue-600 py-16 text-center">
    <div class="mx-auto max-w-xl px-4">
        <h2 class="text-2xl sm:text-3xl font-bold text-white">{{ __('Kami Mendengar Anda!') }}</h2>
        <p class="mt-3 text-sm text-blue-100/80 leading-relaxed">
            {{ __('Punya masukan atau pertanyaan seputar sistem kasir Kasiro? Sampaikan kepada kami.') }}
        </p>
        <a href="{{ auth()->check() ? route('tenants.choose') : route('register') }}"
           class="mt-6 inline-flex items-center gap-2 rounded-full bg-lime-400 px-8 py-2.5 text-sm font-semibold text-slate-900 hover:bg-lime-500 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.3a1 1 0 01.95.68l1 3a1 1 0 01-.27 1.05L8.2 9.2a12 12 0 006.6 6.6l1.47-1.48a1 1 0 011.05-.27l3 1a1 1 0 01.68.95V19a2 2 0 01-2 2A16 16 0 013 5z"/>
            </svg>
            {{ __('Hubungi kami!') }}
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
            <a href="tel:+62" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition" aria-label="{{ __('Telepon') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.3a1 1 0 01.95.68l1 3a1 1 0 01-.27 1.05L8.2 9.2a12 12 0 006.6 6.6l1.47-1.48a1 1 0 011.05-.27l3 1a1 1 0 01.68.95V19a2 2 0 01-2 2A16 16 0 013 5z"/></svg>
            </a>
        </div>
        <p class="text-xs text-blue-100/60">&copy; {{ date('Y') }} kasiro.my.id</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    new Swiper('.feature-swiper', {
        loop: true,
        grabCursor: true,
        speed: 500,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
        },
        navigation: {
            nextEl: '.feature-next',
            prevEl: '.feature-prev',
        },
        pagination: {
            el: '.feature-pagination',
            clickable: true,
            renderBullet(_, className) {
                return `<button class="${className}"></button>`;
            },
        },
    });

    document.querySelectorAll('.scroll-nav-link').forEach((link) => {
        link.addEventListener('click', (e) => {
            const target = document.querySelector(link.getAttribute('href'));
            if (!target) return;
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });
</script>
<style>
    .feature-pagination .swiper-pagination-bullet {
        width: 8px; height: 8px;
        background: #cbd5e1; opacity: 1; border-radius: 9999px;
        transition: width .3s, background .3s;
    }
    .feature-pagination .swiper-pagination-bullet-active {
        width: 20px; background: #334155;
    }
</style>
</body>
</html>
