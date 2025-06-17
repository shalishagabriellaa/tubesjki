# Deployment ke Railway

## Langkah-langkah Deployment

### 1. Persiapan Repository
Pastikan repository sudah di-push ke GitHub dengan file-file berikut:
- `railway.json` - Konfigurasi Railway
- `nixpacks.toml` - Konfigurasi build
- `Procfile` - Command untuk menjalankan aplikasi

### 2. Setup Railway
1. Buka [Railway.app](https://railway.app)
2. Login dengan GitHub account
3. Klik "New Project"
4. Pilih "Deploy from GitHub repo"
5. Pilih repository ini

### 3. Environment Variables
Set environment variables berikut di Railway dashboard berdasarkan konfigurasi aplikasi Anda:

```
APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:aIupQexzScVfox20zI0sGzCTgwpl1yJuONv2JqTQBJ0=
APP_DEBUG=false
APP_URL=https://your-app-name.railway.app

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Database - Gunakan SQLite untuk Railway
DB_CONNECTION=sqlite
DB_DATABASE=/tmp/database.sqlite

# Session dan Cache - Gunakan file untuk Railway
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

CACHE_STORE=file

# Mail - Gunakan log untuk Railway
MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

### 4. Generate APP_KEY (Opsional)
Jika ingin generate APP_KEY baru, jalankan command berikut di Railway terminal:
```bash
php artisan key:generate --force
```

### 5. Database Migration
Jalankan migration untuk setup database:
```bash
php artisan migrate --force
```

### 6. Storage Link
Jika menggunakan file storage:
```bash
php artisan storage:link
```

## Perubahan Konfigurasi untuk Railway

### Database
- **Lokal**: MySQL (`tubesjki`)
- **Railway**: SQLite (`/tmp/database.sqlite`)

### Session & Cache
- **Lokal**: Database
- **Railway**: File (lebih sederhana untuk deployment)

### Queue
- **Lokal**: Database
- **Railway**: Sync (tidak memerlukan queue worker)

### Log Level
- **Lokal**: Debug
- **Railway**: Error (untuk production)

## Troubleshooting

### Error: APP_KEY not set
- Pastikan APP_KEY sudah di-set di environment variables
- Jalankan `php artisan key:generate --force`

### Error: Database connection
- Pastikan database sudah dikonfigurasi dengan benar
- Untuk SQLite, pastikan direktori `/tmp` writable

### Error: Permission denied
- Pastikan direktori `storage` dan `bootstrap/cache` writable
- Jalankan `chmod -R 775 storage bootstrap/cache`

## File Konfigurasi

### railway.json
Konfigurasi deployment Railway dengan health check dan restart policy.

### nixpacks.toml
Konfigurasi build process dengan PHP 8.2 dan semua extension yang diperlukan.

### Procfile
Command untuk menjalankan aplikasi Laravel di Railway. 