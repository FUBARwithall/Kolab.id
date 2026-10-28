# KOLAB.ID — Monorepo CodeIgniter 4

Monorepo terpadu platform **KOLAB.ID** berbasis **CodeIgniter 4** dan database aktif **`kolab_ci4`**.

| Sub-App | Folder | Stack UI | Sasaran & Akses |
| :--- | :--- | :--- | :--- |
| **Landing & Blog** | `landing/` | Tailwind CSS v4 (SSR) | Publik, katalog artikel blog, dan gerbang pemilihan portal (`/login`). |
| **Campaign Management** | `campaign/` | AdminLTE 4 & Bootstrap 5 | Operasional internal brand: **Staff**, **Leader KOL**, dan **Superadmin**. |
| **KOL Data Hub** | `datahub/` | AdminLTE 4 & Bootstrap 5 | Database intelijen & ruang kerja mandiri: **Talent KOL** dan **Superadmin**. |
| **Shared Storage** | `shared/uploads/` | — | Penyimpanan upload runtime (`campaign`, `datahub`, `articles`). |

---

## Prasyarat Sistem (Prerequisites)

- **PHP**: `^8.1`, `^8.2`, atau `^8.3` (Ekstensi wajib: `intl`, `mbstring`, `mysqli`, `curl`, `gd`)
- **Composer**: `v2.x`
- **Database**: MySQL `5.7+` / `8.0+` atau MariaDB `10.4+`

---

## Panduan Instalasi Lokal (Clone to Running)

### 1. Clone Repository
```bash
git clone <repository-url>
cd Kolab.id
```

### 2. Install Dependensi (Composer)
```bash
composer install
```

### 3. Konfigurasi Environment (`.env`)
Salin file konfigurasi contoh:
- **Linux / macOS**:
  ```bash
  cp .env.example .env
  ```
- **Windows (CMD / PowerShell)**:
  ```powershell
  copy .env.example .env
  ```
> Buka file `.env` dan sesuaikan kredensial MySQL jika menggunakan password (`database.default.password`).

### 4. Setup & Import Database (`kolab_ci4`)
Buat database dan import skema bawaan beserta akun awal:
```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS kolab_ci4;"
mysql -u root kolab_ci4 < docs/kolab_ci4.sql
```

### 5. Setup Folder Runtime & Uploads
- **Linux / macOS**:
  ```bash
  bash setup-server.sh
  ```
- **Windows (PowerShell)**:
  ```powershell
  New-Item -ItemType Directory -Force -Path "shared\uploads\campaign", "shared\uploads\datahub"
  ```

---

## Menjalankan Aplikasi di Lokal

### Opsi A: Unified Dev Router (Rekomendasi — 1 Port untuk Semua Sub-App)
Jalankan satu perintah dari root direktori `Kolab.id`:

- **Linux / macOS**:
  ```bash
  php -S localhost:8087 dev-router.php
  ```
- **Windows (CMD / PowerShell)**:
  ```powershell
  php -S localhost:8087 dev-router.php
  ```

Buka peramban (browser):
- **Landing Page & Login Hub** : [http://localhost:8087/](http://localhost:8087/) & [http://localhost:8087/login](http://localhost:8087/login)
- **Campaign Management** : [http://localhost:8087/campaign/](http://localhost:8087/campaign/)
- **KOL Data Hub** : [http://localhost:8087/datahub/](http://localhost:8087/datahub/)

### Opsi B: Standar Multi-Port (`php spark serve`)
Buka 3 terminal terpisah:
```bash
# Terminal 1: Landing Page (Port 8080)
cd landing && php spark serve --port 8080

# Terminal 2: Campaign Management (Port 8000)
cd campaign && php spark serve --port 8000

# Terminal 3: KOL Data Hub (Port 8081)
cd datahub && php spark serve --port 8081
```

---

## Akun Pengguna Bawaan (Default Seeder `kolab_ci4`)

| Role | Email | Password | Akses Portal |
| :--- | :--- | :--- | :--- |
| **Superadmin** | `admin@kolab.id` | `admin123` | Campaign & Datahub (Full Access) |
| **Leader KOL** | `leader@kolab.id` | `admin123` | Campaign (Approval, Strategis & Keuangan) |
| **Staff Operational** | `staff@kolab.id` | `admin123` | Campaign (Tugas Lapangan, Draft, Pengiriman) |
| **Talent KOL** | `kol@kolab.id` | `admin123` | Datahub (Talent KOL Space, Peluang & Tagihan) |

---

## Dokumentasi Teknis Lanjutan
- Rencana Rebuild & Matriks RBAC Lengkap: [docs/REBUILD_PLAN.md](docs/REBUILD_PLAN.md)
