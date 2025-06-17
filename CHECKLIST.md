# Checklist Deployment Railway

## ✅ Persiapan
- [ ] Repository sudah di-push ke GitHub
- [ ] File `railway.json` sudah ada
- [ ] File `nixpacks.toml` sudah ada
- [ ] File `Procfile` sudah ada

## ✅ Setup Railway
- [ ] Login ke Railway.app dengan GitHub
- [ ] Buat project baru
- [ ] Pilih repository `tubesjki`
- [ ] Klik "Deploy Now"
- [ ] Tunggu build selesai (5-10 menit)

## ✅ Environment Variables
- [ ] APP_NAME=Laravel
- [ ] APP_ENV=production
- [ ] APP_KEY=base64:aIupQexzScVfox20zI0sGzCTgwpl1yJuONv2JqTQBJ0=
- [ ] APP_DEBUG=false
- [ ] APP_URL=https://your-app-name.railway.app
- [ ] DB_CONNECTION=sqlite
- [ ] DB_DATABASE=/tmp/database.sqlite
- [ ] SESSION_DRIVER=file
- [ ] CACHE_STORE=file
- [ ] QUEUE_CONNECTION=sync
- [ ] LOG_LEVEL=error

## ✅ Database Setup
- [ ] Buka terminal Railway
- [ ] Jalankan: `php artisan migrate --force`
- [ ] Jalankan: `php artisan storage:link` (jika diperlukan)

## ✅ Verifikasi
- [ ] Status deployment "Deployed"
- [ ] Buka URL Railway
- [ ] Aplikasi Laravel berjalan normal
- [ ] Tidak ada error di logs

## 🔧 Troubleshooting
Jika ada error:
- [ ] Cek logs di Railway dashboard
- [ ] Jalankan: `php artisan key:generate --force`
- [ ] Jalankan: `composer install --no-dev --optimize-autoloader`
- [ ] Jalankan: `chmod -R 775 storage bootstrap/cache`

## 📝 Catatan
- URL Railway: ________________
- Tanggal deploy: ________________
- Status: ________________ 