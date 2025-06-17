# Langkah-langkah Setup Railway

## 1. Persiapan Repository GitHub

### Pastikan repository sudah di-push ke GitHub:
```bash
# Jika belum ada remote origin
git remote add origin https://github.com/username/tubesjki.git

# Push ke GitHub
git add .
git commit -m "Add Railway deployment configuration"
git push origin main
```

## 2. Setup Railway Project

### Langkah 1: Buka Railway
1. Buka browser dan kunjungi [Railway.app](https://railway.app)
2. Klik "Login with GitHub"
3. Authorize Railway untuk mengakses GitHub account Anda

### Langkah 2: Buat Project Baru
1. Setelah login, klik tombol **"New Project"**
2. Pilih **"Deploy from GitHub repo"**
3. Cari dan pilih repository `tubesjki` Anda
4. Klik **"Deploy Now"**

### Langkah 3: Tunggu Build
- Railway akan otomatis mendeteksi ini adalah aplikasi Laravel
- Build process akan dimulai (biasanya 5-10 menit)
- Anda bisa melihat progress di dashboard Railway

## 3. Konfigurasi Environment Variables

### Langkah 1: Buka Environment Variables
1. Di dashboard Railway, klik tab **"Variables"**
2. Klik **"New Variable"** untuk menambahkan satu per satu

### Langkah 2: Set Environment Variables
Tambahkan variable berikut satu per satu:

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

DB_CONNECTION=sqlite
DB_DATABASE=/tmp/database.sqlite

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

CACHE_STORE=file

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="Laravel"

VITE_APP_NAME="Laravel"
```

**Catatan Penting:**
- Ganti `your-app-name.railway.app` dengan URL yang diberikan Railway
- URL akan muncul setelah deployment pertama berhasil

## 4. Setup Database

### Langkah 1: Jalankan Migration
1. Di dashboard Railway, klik tab **"Deployments"**
2. Klik deployment terbaru
3. Klik **"View Logs"**
4. Klik **"Open Terminal"**
5. Jalankan command:
```bash
php artisan migrate --force
```

### Langkah 2: Generate Storage Link (Jika diperlukan)
```bash
php artisan storage:link
```

## 5. Verifikasi Deployment

### Langkah 1: Cek Status
1. Di dashboard Railway, pastikan status **"Deployed"**
2. Klik **"View"** untuk membuka aplikasi

### Langkah 2: Test Aplikasi
1. Buka URL yang diberikan Railway
2. Pastikan aplikasi Laravel berjalan dengan baik
3. Cek apakah ada error di logs

## 6. Troubleshooting

### Error: "No application encryption key has been specified"
**Solusi:**
1. Buka terminal Railway
2. Jalankan: `php artisan key:generate --force`

### Error: "Database connection failed"
**Solusi:**
1. Pastikan `DB_CONNECTION=sqlite` sudah diset
2. Jalankan: `php artisan migrate --force`

### Error: "Permission denied"
**Solusi:**
1. Buka terminal Railway
2. Jalankan: `chmod -R 775 storage bootstrap/cache`

### Error: "Class not found"
**Solusi:**
1. Buka terminal Railway
2. Jalankan: `composer install --no-dev --optimize-autoloader`

## 7. Monitoring & Maintenance

### View Logs
- Klik tab **"Deployments"** → **"View Logs"**
- Monitor error dan performance

### Restart Application
- Klik **"Redeploy"** jika perlu restart

### Update Application
- Push perubahan baru ke GitHub
- Railway akan otomatis redeploy

## 8. Custom Domain (Opsional)

### Setup Custom Domain
1. Klik tab **"Settings"**
2. Scroll ke **"Domains"**
3. Klik **"Generate Domain"** atau **"Custom Domain"**
4. Ikuti instruksi untuk setup DNS

## Tips Penting

1. **Simpan URL Railway** - Catat URL yang diberikan Railway
2. **Monitor Logs** - Selalu cek logs jika ada masalah
3. **Backup Database** - Jika menggunakan database eksternal
4. **Environment Variables** - Jangan lupa set semua variable yang diperlukan
5. **APP_DEBUG=false** - Pastikan debug mode dimatikan di production

## Support

Jika mengalami masalah:
1. Cek logs di Railway dashboard
2. Pastikan semua environment variables sudah diset
3. Coba redeploy aplikasi
4. Hubungi support Railway jika masih bermasalah 