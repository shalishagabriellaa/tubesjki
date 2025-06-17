#!/bin/bash

echo "🚀 Mempersiapkan deployment Laravel ke Railway..."

# Check if git is initialized
if [ ! -d ".git" ]; then
    echo "❌ Repository Git belum diinisialisasi. Jalankan 'git init' terlebih dahulu."
    exit 1
fi

# Add all files
echo "📁 Menambahkan file-file baru..."
git add .

# Check if there are changes to commit
if [ -z "$(git status --porcelain)" ]; then
    echo "✅ Tidak ada perubahan yang perlu di-commit"
else
    echo "💾 Committing perubahan..."
    git commit -m "Add Railway deployment configuration"
fi

# Check if remote origin exists
if ! git remote get-url origin > /dev/null 2>&1; then
    echo "❌ Remote origin belum dikonfigurasi."
    echo "   Tambahkan remote GitHub Anda dengan:"
    echo "   git remote add origin https://github.com/username/repository.git"
    exit 1
fi

# Push to GitHub
echo "📤 Pushing ke GitHub..."
git push origin main

echo ""
echo "✅ Repository berhasil di-push ke GitHub!"
echo ""
echo "📋 Langkah selanjutnya:"
echo "1. Buka Railway.app dan login dengan GitHub"
echo "2. Buat project baru dan pilih repository ini"
echo "3. Set environment variables sesuai DEPLOYMENT.md"
echo "4. Deploy dan tunggu build selesai"
echo ""
echo "📖 Lihat DEPLOYMENT.md untuk panduan lengkap" 