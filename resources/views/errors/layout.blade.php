<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') &middot; {{ config('app.name', 'Kasiro') }}</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{
            font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
            min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;
            padding:1.5rem;color:#1e293b;position:relative;overflow:hidden;
            background:linear-gradient(45deg,#1e40af 0%,#2563eb 55%,#3b82f6 100%);
        }
        .glow{position:absolute;width:20rem;height:20rem;border-radius:9999px;background:rgba(255,255,255,.10);filter:blur(64px);pointer-events:none}
        .glow.tl{top:-6rem;left:-6rem}
        .glow.br{bottom:-6rem;right:-6rem}
        .logo{height:1.75rem;width:auto;margin-bottom:1.5rem;position:relative}
        .card{
            width:100%;max-width:30rem;background:#fff;border-radius:1.5rem;
            box-shadow:0 25px 50px -12px rgba(30,58,138,.45);
            padding:3rem 2rem;text-align:center;position:relative;
        }
        .badge{
            width:5rem;height:5rem;margin:0 auto 1.5rem;border-radius:9999px;
            display:flex;align-items:center;justify-content:center;
            background:@yield('badge-bg', '#eff6ff');
        }
        .badge svg{width:2.5rem;height:2.5rem;stroke:@yield('badge-fg', '#2563eb');fill:none;stroke-width:1.8}
        .code{
            font-size:4.5rem;line-height:1;font-weight:800;letter-spacing:-.05em;
            background:linear-gradient(135deg,#1d4ed8,#3b82f6);
            -webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;
            margin-bottom:.75rem;
        }
        h1{font-size:1.5rem;font-weight:700;margin-bottom:.5rem}
        p{color:#64748b;font-size:.95rem;line-height:1.6;margin-bottom:2rem}
        .btn{
            display:inline-block;padding:.8rem 2rem;border-radius:9999px;
            background:#a3e635;color:#1e293b;font-weight:600;font-size:.9rem;
            text-decoration:none;transition:background .15s;
        }
        .btn:hover{background:#84cc16}
        .home-link{display:block;margin-top:1rem;color:#94a3b8;font-size:.8rem;text-decoration:none}
        .home-link:hover{color:#64748b}
    </style>
</head>
<body>
    <div class="glow tl"></div>
    <div class="glow br"></div>

    <a href="{{ url('/') }}">
        <img class="logo" src="{{ asset('images/kasiro-logo-white.png') }}" alt="{{ config('app.name', 'Kasiro') }}">
    </a>

    <div class="card">
        <div class="badge">
            @yield('icon')
        </div>
        <div class="code">@yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a href="{{ url('/') }}" class="btn">{{ __('Kembali ke Beranda') }}</a>
        <a href="javascript:history.back()" class="home-link">&larr; {{ __('Kembali ke halaman sebelumnya') }}</a>
    </div>
</body>
</html>
