<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') &middot; {{ config('app.name', 'Kasiro') }}</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{
            font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
            min-height:100vh;display:flex;align-items:center;justify-content:center;
            padding:1.5rem;color:#1e293b;
            background:linear-gradient(135deg,#eef2ff 0%,#faf5ff 50%,#fff1f2 100%);
        }
        .card{
            width:100%;max-width:30rem;background:#fff;border-radius:1.5rem;
            box-shadow:0 20px 50px -12px rgba(0,0,0,.18);
            padding:3rem 2rem;text-align:center;
        }
        .badge{
            width:5rem;height:5rem;margin:0 auto 1.5rem;border-radius:9999px;
            display:flex;align-items:center;justify-content:center;
            background:@yield('badge-bg', '#eef2ff');
        }
        .badge svg{width:2.5rem;height:2.5rem;stroke:@yield('badge-fg', '#6366f1');fill:none;stroke-width:1.8}
        .code{
            font-size:4.5rem;line-height:1;font-weight:800;letter-spacing:-.05em;
            background:linear-gradient(135deg,#6366f1,#a855f7);
            -webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;
            margin-bottom:.75rem;
        }
        h1{font-size:1.5rem;font-weight:700;margin-bottom:.5rem}
        p{color:#64748b;font-size:.95rem;line-height:1.6;margin-bottom:2rem}
        .btn{
            display:inline-block;padding:.75rem 1.75rem;border-radius:.85rem;
            background:#4f46e5;color:#fff;font-weight:600;font-size:.9rem;
            text-decoration:none;transition:background .15s;
        }
        .btn:hover{background:#4338ca}
        .home-link{display:block;margin-top:1rem;color:#94a3b8;font-size:.8rem;text-decoration:none}
        .home-link:hover{color:#64748b}
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">
            @yield('icon')
        </div>
        <div class="code">@yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a href="{{ url('/') }}" class="btn">Kembali ke Beranda</a>
        <a href="javascript:history.back()" class="home-link">&larr; Kembali ke halaman sebelumnya</a>
    </div>
</body>
</html>
