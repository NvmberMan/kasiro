# Kasiro — Local Development Setup

Kasiro is a single-codebase, single-database multi-tenant app. Every tenant is
served from a subdomain of the central domain `kasiro.com`, resolved at runtime
from the request host. Local dev therefore needs **`kasiro.com` and any
`*.kasiro.com` to point at your machine**.

## 1. Database

MySQL (Laragon, port `3306`, user `root`, empty password by default).

```sql
CREATE DATABASE IF NOT EXISTS kasiro_remake
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

`.env` is already configured:

```dotenv
APP_URL=http://kasiro.com
DB_CONNECTION=mysql
DB_DATABASE=kasiro_remake
SESSION_DRIVER=database
SESSION_DOMAIN=.kasiro.com   # shares the auth cookie across subdomains (Milestone 2)
```

Then:

```bash
php artisan migrate
```

## 2. Web server vhost (Laragon Apache/Nginx)

Point both the apex and the wildcard at the project's `public/` directory.

**Apache** (`httpd-vhosts.conf`):

```apache
<VirtualHost *:80>
    ServerName kasiro.com
    ServerAlias *.kasiro.com
    DocumentRoot "T:/Programs/laragon/www/kasiro_remake/public"
    <Directory "T:/Programs/laragon/www/kasiro_remake/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx**:

```nginx
server {
    listen 80;
    server_name kasiro.com *.kasiro.com;
    root T:/Programs/laragon/www/kasiro_remake/public;
    index index.php;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

Restart Laragon after editing.

## 3. Wildcard DNS on Windows

The Windows `hosts` file **does not support wildcards** (`*.kasiro.com`), so you
cannot map every future tenant subdomain there. Two options:

### Option A — Acrylic DNS Proxy (recommended)

1. Install [Acrylic DNS Proxy](https://mayakron.altervista.org/support/acrylic/Home.htm).
2. Edit `AcrylicHosts.txt`, add:

   ```
   127.0.0.1  kasiro.com
   127.0.0.1  *.kasiro.com
   ```

3. Restart the Acrylic service ("Purge…" then "Restart Acrylic Service").
4. Set your network adapter's **preferred DNS** to `127.0.0.1`.

Now `kasiro.com` and any `<anything>.kasiro.com` resolve to localhost — new
tenants work with no further config.

### Option B — manual hosts entries (quick, no wildcard)

Good enough for a few fixed dev tenants. Edit
`C:\Windows\System32\drivers\etc\hosts` (as Administrator):

```
127.0.0.1  kasiro.com
127.0.0.1  www.kasiro.com
127.0.0.1  warungbudi.kasiro.com
127.0.0.1  lawasstore.kasiro.com
```

Add one line per tenant subdomain you want to test.

## 4. Verifying without DNS (Host header)

The tenant resolver reads the HTTP `Host` header, so you can verify routing with
`php artisan serve` and `curl` — no DNS/vhost needed:

```bash
php artisan serve --host=127.0.0.1 --port=8000

curl -H "Host: kasiro.com"            http://127.0.0.1:8000/   # platform
curl -H "Host: warungbudi.kasiro.com" http://127.0.0.1:8000/   # resolves tenant
curl -H "Host: admin.kasiro.com"      http://127.0.0.1:8000/   # reserved -> 404
curl -H "Host: ghost.kasiro.com"      http://127.0.0.1:8000/   # unknown  -> 404
```

## 5. Tests

Feature/unit tests run on an in-memory SQLite DB (see `phpunit.xml`) and simulate
hosts via full URLs, so they need neither MySQL nor DNS:

```bash
php artisan test
```

The cross-tenant isolation suite (`tests/Feature/TenantIsolationTest.php`) is a
**CRITICAL gate** — it must stay green before any tenant-scoped feature is added.

## Notes

- Reserved subdomains (`www`, `app`, `api`, `admin`, …) live in
  `config/tenancy.php` and are rejected both at resolution and at tenant
  creation (`App\Rules\ValidSubdomain`).
- Tenant lookups are cached for `TENANCY_CACHE_TTL` seconds (default 300).
  Cross-subdomain shared sessions are exercised in Milestone 2.
