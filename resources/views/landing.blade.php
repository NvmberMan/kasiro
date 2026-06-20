<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kasiro — POS Bermerek untuk UMKM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-white text-gray-900">

{{-- Nav --}}
<header class="border-b border-gray-100 bg-white/80 backdrop-blur sticky top-0 z-50">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 flex items-center justify-between h-14">
        <a href="/" class="text-xl font-bold text-indigo-600 tracking-tight">Kasiro</a>
        <nav class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="text-sm text-gray-600 hover:text-gray-900 transition">Dashboard</a>
                <a href="{{ route('tenants.choose') }}"
                   class="text-sm font-medium bg-indigo-600 text-white px-4 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                    Buat Toko
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="text-sm text-gray-600 hover:text-gray-900 transition">Masuk</a>
                <a href="{{ route('register') }}"
                   class="text-sm font-medium bg-indigo-600 text-white px-4 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                    Daftar Gratis
                </a>
            @endauth
        </nav>
    </div>
</header>

{{-- Hero --}}
<section class="py-20 sm:py-28 text-center px-4">
    <div class="mx-auto max-w-2xl">
        <span class="inline-block mb-4 text-xs font-semibold tracking-widest text-indigo-500 uppercase">
            Kasir Digital untuk UMKM
        </span>
        <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight text-gray-900">
            POS bermerek milikmu,<br class="hidden sm:block"> siap dalam hitungan menit.
        </h1>
        <p class="mt-5 text-lg text-gray-500 leading-relaxed">
            Buat aplikasi kasir dengan nama & warna brandmu sendiri.
            Undang karyawan, kelola produk, lihat laporan — semua dalam satu platform.
        </p>
        <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3">
            @auth
                <a href="{{ route('tenants.choose') }}"
                   class="px-7 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition text-sm">
                    Buat Toko Baru
                </a>
                <a href="{{ route('dashboard') }}"
                   class="px-7 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition text-sm">
                    Ke Dashboard
                </a>
            @else
                <a href="{{ route('register') }}"
                   class="px-7 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition text-sm">
                    Mulai Gratis
                </a>
                <a href="{{ route('tenants.showcase') }}"
                   class="px-7 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition text-sm">
                    Lihat Template
                </a>
            @endauth
        </div>
    </div>
</section>

{{-- Features --}}
<section class="py-16 bg-gray-50">
    <div class="mx-auto max-w-5xl px-4 sm:px-6">
        <h2 class="text-center text-2xl font-bold text-gray-800 mb-10">Semua yang kamu butuhkan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="text-3xl mb-3">🛒</div>
                <h3 class="font-semibold text-gray-900 mb-1">POS Bermerek</h3>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Tampilan kasir dengan warna, logo, dan nama tokomu sendiri.
                    Subdomain khusus: <span class="font-mono text-indigo-600">tokomu.kasiro.com</span>
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="text-3xl mb-3">👥</div>
                <h3 class="font-semibold text-gray-900 mb-1">Manajemen Karyawan</h3>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Undang kasir & manager lewat link. Atur role, revoke akses
                    kapan saja — tanpa berbagi password.
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="text-3xl mb-3">📊</div>
                <h3 class="font-semibold text-gray-900 mb-1">Laporan Penjualan</h3>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Penjualan harian, produk terlaris, dan ringkasan bulanan
                    langsung di dashboard.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- How it works --}}
<section class="py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6">
        <h2 class="text-center text-2xl font-bold text-gray-800 mb-10">Cara kerja</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
            <div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 font-bold flex items-center justify-center mx-auto mb-3 text-lg">1</div>
                <h4 class="font-semibold text-gray-900 mb-1">Daftar akun</h4>
                <p class="text-sm text-gray-500">Gratis, tanpa kartu kredit.</p>
            </div>
            <div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 font-bold flex items-center justify-center mx-auto mb-3 text-lg">2</div>
                <h4 class="font-semibold text-gray-900 mb-1">Pilih template</h4>
                <p class="text-sm text-gray-500">Atau buat desain sendiri dari nol.</p>
            </div>
            <div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 font-bold flex items-center justify-center mx-auto mb-3 text-lg">3</div>
                <h4 class="font-semibold text-gray-900 mb-1">Mulai bertransaksi</h4>
                <p class="text-sm text-gray-500">Toko langsung aktif, kasir siap dipakai.</p>
            </div>
        </div>
    </div>
</section>

{{-- Template showcase --}}
@if ($templates->isNotEmpty())
<section class="py-16 bg-gray-50">
    <div class="mx-auto max-w-5xl px-4 sm:px-6">
        <h2 class="text-center text-2xl font-bold text-gray-800 mb-2">Template siap pakai</h2>
        <p class="text-center text-sm text-gray-500 mb-8">Pilih satu dan toko kamu langsung jadi.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($templates as $template)
                @php
                    $config = $template->default_config ?? [];
                    $palette = config('branding.palettes.'.(($config['color_palette'] ?? 'default')), config('branding.palettes.default'));
                @endphp
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                    {{-- Color preview bar --}}
                    <div class="h-2 w-full" style="background: {{ $palette['--brand-primary'] ?? '#6366f1' }};"></div>

                    {{-- Preview image or gradient --}}
                    <div class="h-32 flex items-center justify-center text-sm"
                         style="background: {{ $palette['--brand-bg'] ?? '#fff' }}; color: {{ $palette['--brand-fg'] ?? '#111' }};">
                        @if ($template->preview_image)
                            <img src="{{ asset('storage/'.$template->preview_image) }}"
                                 alt="{{ $template->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="text-center px-4 opacity-60">
                                <div class="font-bold text-base">{{ $template->name }}</div>
                                <div class="text-xs mt-1 font-mono" style="color:{{ $palette['--brand-primary'] }}">
                                    {{ $config['color_palette'] ?? 'default' }} · {{ $config['layout'] ?? 'modern' }}
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="p-4 flex-1 flex flex-col gap-3">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $template->name }}</h3>
                            @if ($template->description)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $template->description }}</p>
                            @endif
                        </div>

                        {{-- Palette swatch --}}
                        <div class="flex gap-1.5">
                            @foreach (['--brand-primary','--brand-accent','--brand-bg','--brand-fg'] as $var)
                                <span class="w-5 h-5 rounded-full border border-gray-200"
                                      style="background:{{ $palette[$var] ?? '#eee' }};"></span>
                            @endforeach
                        </div>

                        @auth
                            <a href="{{ route('tenants.showcase') }}"
                               class="block text-center text-sm font-medium bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                                Pakai Template Ini
                            </a>
                        @else
                            <a href="{{ route('register') }}"
                               class="block text-center text-sm font-medium bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                                Daftar & Pakai Ini
                            </a>
                        @endauth
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('tenants.showcase') }}"
               class="text-sm text-indigo-600 hover:underline font-medium">
                Lihat semua template →
            </a>
        </div>
    </div>
</section>
@endif

{{-- CTA bottom --}}
<section class="py-20 text-center px-4">
    <div class="mx-auto max-w-xl">
        <h2 class="text-3xl font-bold text-gray-900 mb-3">Siap buka toko digitalmu?</h2>
        <p class="text-gray-500 mb-7">Gratis. Langsung aktif. Tidak perlu keahlian teknis.</p>
        @auth
            <a href="{{ route('tenants.choose') }}"
               class="inline-block px-8 py-3.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition">
                Buat Toko Baru
            </a>
        @else
            <a href="{{ route('register') }}"
               class="inline-block px-8 py-3.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition">
                Mulai Gratis Sekarang
            </a>
        @endauth
    </div>
</section>

{{-- Footer --}}
<footer class="border-t border-gray-100 py-6 text-center text-xs text-gray-400">
    &copy; {{ date('Y') }} Kasiro. Semua hak dilindungi.
</footer>

</body>
</html>
