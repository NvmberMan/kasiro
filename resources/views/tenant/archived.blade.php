<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Aplikasi tidak aktif') }}</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background: #f8fafc; color: #1e293b; display: flex; min-height: 100vh; align-items: center; justify-content: center; margin: 0; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 2.5rem; max-width: 28rem; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        h1 { font-size: 1.25rem; margin: 0 0 .5rem; }
        p { color: #64748b; line-height: 1.6; margin: 0; }
        .brand { font-weight: 600; color: #0f172a; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ __('Aplikasi kasir ini sedang tidak aktif') }}</h1>
        <p>
            {!! __('<span class="brand">:name</span> telah diarsipkan oleh pemiliknya dan untuk sementara tidak dapat diakses.', ['name' => e($tenant->name)]) !!}
        </p>
    </div>
</body>
</html>
