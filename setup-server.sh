#!/bin/bash
# ============================================================
# setup-server.sh — dijalankan SEKALI via cPanel Terminal
# setelah repo monorepo CodeIgniter 4 selesai di-clone.
#
# Yang dilakukannya:
#   1. Membuat folder live docroot: ~/Kolab.id/{landing,campaign,datahub}
#   2. Membuat struktur writable (cache, logs, session, uploads) + permission 775
#   3. Membuat shared/uploads + symlink uploads ke public/uploads per app
# ============================================================
set -e

ROOT="${1:-$HOME/Kolab.id}"

echo "== Menyiapkan struktur monorepo CI4 di: $ROOT =="

# 1. Pastikan folder writable memiliki permission 775
for APP in landing campaign datahub; do
    echo "-> Menyiapkan writable untuk: $APP"
    mkdir -p "$ROOT/$APP/writable/cache" \
             "$ROOT/$APP/writable/logs" \
             "$ROOT/$APP/writable/session" \
             "$ROOT/$APP/writable/debugbar" \
             "$ROOT/$APP/writable/uploads"
    chmod -R 775 "$ROOT/$APP/writable" 2>/dev/null || true
done

# 2. Folder upload bersama (shared)
echo "-> Menyiapkan shared/uploads..."
mkdir -p "$ROOT/shared/uploads/campaign" "$ROOT/shared/uploads/datahub"
chmod -R 775 "$ROOT/shared/uploads" 2>/dev/null || true

# 3. Symlink uploads ke public/ per app (idempotent — hanya bila belum ada)
# Catatan CI4: Symlink mengarah 2 tingkat ke atas (../../shared/uploads/...)
echo "-> Membuat symlink uploads di public/..."
[ -e "$ROOT/campaign/public/uploads" ] || ln -s ../../shared/uploads/campaign "$ROOT/campaign/public/uploads"
[ -e "$ROOT/datahub/public/uploads" ]  || ln -s ../../shared/uploads/datahub  "$ROOT/datahub/public/uploads"

echo ""
echo "Selesai. Verifikasi symlink:"
ls -l "$ROOT/campaign/public/uploads" "$ROOT/datahub/public/uploads" 2>/dev/null || true
echo ""
echo "Langkah berikutnya di cPanel:"
echo "  1. Pastikan Document Root subdomain di cPanel mengarah ke folder public/:"
echo "     - kolab.id         -> ~/Kolab.id/landing/public"
echo "     - app.kolab.id     -> ~/Kolab.id/campaign/public"
echo "     - datahub.kolab.id -> ~/Kolab.id/datahub/public"
echo "  2. Salin template env ke .env di server dan sesuaikan kredensial DB/API."
echo "  3. Deploy HEAD Commit via cPanel Git Version Control."
