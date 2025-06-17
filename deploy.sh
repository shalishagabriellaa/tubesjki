#!/bin/bash

echo "🚀 Memulai deployment Laravel ke Railway..."

# Check if git is initialized
if [ ! -d ".git" ]; then
    echo "❌ Repository Git belum diinisialisasi. Jalankan 'git init' terlebih dahulu."
    exit 1
fi

# Check if files are committed
if [ -n "$(git status --porcelain)" ]; then
    echo "⚠️  Ada file yang belum di-commit. Commit dulu perubahan Anda:"
    echo "   git add ."
    echo "   git commit -m 'Prepare for Railway deployment'"
    exit 1
fi

# Check if remote origin exists
if ! git remote get-url origin > /dev/null 2>&1; then
    echo "❌ Remote origin belum dikonfigurasi. Tambahkan remote GitHub Anda:"
    echo "   git remote add origin https://github.com/username/repository.git"
    exit 1
fi

echo "✅ Repository siap untuk deployment"
echo ""
echo "📋 Langkah selanjutnya:"
echo "1. Push ke GitHub: git push origin main"
echo "2. Buka Railway.app dan login dengan GitHub"
echo "3. Buat project baru dan pilih repository ini"
echo "4. Set environment variables sesuai DEPLOYMENT.md"
echo "5. Deploy dan tunggu build selesai"
echo ""
echo "📖 Lihat DEPLOYMENT.md untuk panduan lengkap" 