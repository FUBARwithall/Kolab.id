# Panduan & Rencana Rebuild KOL-System-Apps (CI3) ke Kolab.id (CI4)

> **Repositori Sumber (CI3):** `/home/ahmadsaif/project/magang/KOL-System-Apps` (Cicool Builder, AdminLTE 2)  
> **Repositori Target (CI4):** `/home/ahmadsaif/project/magang/Kolab.id`  
> **Starter Kit Backoffice:** [Adel-Qusay/Codeigniter-4-CRUD-generator](https://github.com/Adel-Qusay/Codeigniter-4-CRUD-generator) (AdminLTE 4, Bootstrap 5)  
> **Database Utama Aktif:** **`kolab_ci4`** (MySQL)  
> **Arsitektur Deployment:** **Monorepo 3 Sub-App** (`landing/`, `campaign/`, `datahub/`)

---

## 1. Arsitektur Terkini & Pembagian Gerbang Portal

Berdasarkan keputusan arsitektur terbaru dan pemisahan RBAC yang telah selesai diimplementasikan, platform dibagi menjadi **2 Portal Backoffice Utama + 1 Web Publik**:

```text
Kolab.id/ (Monorepo Root)
│
├── landing/                       <-- Sub-App 1: Web Publik & Blog SSR (Tailwind CSS v4)
│   ├── app/Controllers/          (Home.php, Artikel.php, Auth.php)
│   ├── app/Views/                (landing/, blog/, login/)
│   └── public/                   (dist/output.css, assets/, index.php)
│
├── campaign/                      <-- Sub-App 2: Campaign Management (AdminLTE 4 - Tema Biru)
│   ├── app/Controllers/          (Dashboard.php, Auth.php, ArticlesApi.php)
│   ├── app/Filters/              (AuthFilter.php -> auth:admin,leader,staff)
│   ├── app/Views/                (auth/, dashboard/, layout/sidebar.php)
│   ├── crud-generator/           (Generator CRUD Adel AdminLTE 4)
│   └── public/                   (asset/, index.php)
│
├── datahub/                       <-- Sub-App 3: KOL Data Hub & Ruang Talent (AdminLTE 4 - Tema Hijau)
│   ├── app/Controllers/          (Dashboard.php, Auth.php)
│   ├── app/Filters/              (AuthFilter.php -> auth:admin,kol)
│   ├── app/Views/                (auth/, dashboard/, talent/, layout/sidebar.php)
│   └── public/                   (asset/, index.php)
│
├── shared/
│   └── uploads/                   <-- Penyimpanan berkas upload bersama runtime
├── docs/
│   ├── kolab_ci4.sql              <-- Dump skema & data seeder default DB kolab_ci4
│   └── REBUILD_PLAN.md            <-- Dokumen rencana ini
├── dev-router.php                 <-- Server Lokal Terpadu (php -S localhost:8087 dev-router.php)
├── setup-server.sh                <-- Otomasi permission & symlink runtime
└── composer.json                  <-- Shared dependencies core CI4 framework
```

---

## 2. Pemetaan Gerbang Akses & Rute

| Endpoint | Sub-App | Sasaran Pengguna | Stack UI | Cakupan Fitur |
| :--- | :--- | :--- | :--- | :--- |
| **`/`** & **`/artikel`** | `landing/` | Publik & Pengunjung | Tailwind CSS v4 (SSR) | Profil perusahaan, layanan, katalog artikel blog SSR via API internal, dan gerbang pemilihan login portal (`/login`). |
| **`/campaign/*`** | `campaign/` | **Superadmin**, **Leader KOL**, **Staff Operational** | AdminLTE 4 (Tema Biru) | Manajemen kampanye, penyusunan brief konten, alur approval draft video, pelacakan logistik sampel produk, monitoring postingan live, dan approval keuangan. |
| **`/datahub/*`** | `datahub/` | **Superadmin** & **Talent KOL** | AdminLTE 4 (Tema Hijau) | **Superadmin**: Intelijen database KOL, metrik platform medsos, rate cards industri, scraper Apify, tiers & kategori.<br>**Talent KOL**: Ruang mandiri (kampanye terbuka, status lamaran, tugas konten, kontrak, rate card pribadi, tagihan invoice). |
| **`/KOL/*`** (Legacy) | — | Influencer / KOL | — | Otomatis di-redirect 302 ke `/datahub/dashboard` melalui [dev-router.php](file:///home/ahmadsaif/project/magang/Kolab.id/dev-router.php). |

---

## 3. Matriks Hak Akses (RBAC) Berdasarkan Database `kolab_ci4`

Autentikasi dikelola secara ketat berbasis tabel `users`, `roles`, dan `user_roles` di database **`kolab_ci4`**:

| Modul / Menu | Rute | Superadmin (`admin`) | Leader KOL (`leader`) | Staff Operational (`staff`) | Talent KOL (`kol`) |
| :--- | :--- | :---: | :---: | :---: | :---: |
| **Portal Campaign** | `/campaign/login` | Akses Penuh | Akses Penuh | Akses Penuh | **DITOLAK** (Redirect ke Datahub) |
| Dashboard Campaign | `/campaign/dashboard` |  Full |  Strategis |  Operasional |  |
| Master Campaigns | `/campaign/campaigns` |  CRUD |  CRUD |  Pantau |  |
| Briefs & Guidelines | `/campaign/briefs` |  Full Approval |  Approval |  Review Operasional |  |
| Review Draft Video | `/campaign/drafts` |  Approval Akhir |  Approval Akhir |  Review & Catatan |  |
| Pengiriman Sampel | `/campaign/shipments` |  Full |  Pantau |  Update Resi/Kirim |  |
| Keuangan & Kontrak | `/campaign/finance` |  Full Eksekusi |  Approval |  (Tersembunyi) |  |
| CRUD Generator Adel | `/campaign/crud-generator` |  Akses Penuh |  (Tersembunyi) |  (Tersembunyi) |  |
| **Portal Datahub** | `/datahub/login` | Akses Penuh | **DITOLAK** (Redirect ke Campaign) | **DITOLAK** (Redirect ke Campaign) | Akses Penuh |
| Dashboard Datahub | `/datahub/dashboard` |  Intelijen Datahub |  |  |  Ringkasan Pasar |
| Kampanye Terbuka | `/datahub/open-campaigns` |  (Tersembunyi) |  |  |  Katalog Peluang |
| Lamaran Saya | `/datahub/my-applications` |  (Tersembunyi) |  |  |  Status Seleksi |
| Tugas Konten Saya | `/datahub/my-tasks` |  (Tersembunyi) |  |  |  Submit Draft & Timeline |
| Kontrak Kerja Sama | `/datahub/my-contracts` |  (Tersembunyi) |  |  |  Dokumen Perjanjian |
| Rate Card & Invoices | `/datahub/my-rates`, `/my-invoices` |  (Tersembunyi) |  |  |  Tarif & Penagihan Fee |
| Master Database KOL | `/datahub/kols` |  CRUD & Approval |  |  |  Benchmark Direktori |
| Apify Scraper Engine | `/datahub/scraper` |  Full Eksekusi |  |  |  (Tersembunyi) |

---

## 4. Status Progres Rebuild Saat Ini

### Fase 1: Fondasi Arsitektur & RBAC Multi-Portal
- [x] Struktur Monorepo 3 sub-app (`landing/`, `campaign/`, `datahub/`) terpisah secara bersih.
- [x] Konfigurasi database tunggal `kolab_ci4` di seluruh sub-app via `.env`.
- [x] Integrasi generator CRUD Adel AdminLTE 4 & Bootstrap 5.
- [x] Implementasi sistem Autentikasi `UserModel` berbasis hash Bcrypt pada tabel `kolab_ci4.users`.
- [x] Pemisahan total Sidebar & filter otorisasi `AuthFilter`:
  - Campaign hanya untuk `admin`, `leader`, `staff`.
  - Datahub hanya untuk `admin`, `kol`.
- [x] Halaman login terpadu (`/login`) dengan 2 opsi portal.
- [x] Unified Dev Router ([dev-router.php](file:///home/ahmadsaif/project/magang/Kolab.id/dev-router.php)) untuk menjalankan seluruh portal dalam satu port lokal (`8087`).
- [x] Dump database awal [docs/kolab_ci4.sql](file:///home/ahmadsaif/project/magang/Kolab.id/docs/kolab_ci4.sql) untuk kemudahan onboarding lokal.

### Fase 2: Endpoint `/` — Landing Page & Blog Publik
- [x] Controller Landing (`Home.php`, `Artikel.php`, `Auth.php`).
- [x] View SSR dengan Tailwind CSS v4 (`dist/output.css`).
- [x] Pustaka `ArtikelApi` untuk membaca konten feed dari endpoint internal Campaign API `/campaign/articles/feed` (decoupled, tanpa query DB langsung dari landing).
- [ ] Optimasi sitemap XML & SEO meta generator otomatis.

### Fase 3: Endpoint `/datahub` — Intelijen KOL & Ruang Talent
- [x] Dashboard Intelijen untuk Superadmin & Dashboard Pasar untuk Talent KOL.
- [x] Rute dan controller method Ruang Talent KOL:
  - `openCampaigns()`, `myApplications()`, `myTasks()`, `myContracts()`, `myRates()`, `myInvoices()`.
- [x] View templates Adel responsif untuk ruang talent di `datahub/app/Views/talent/`.
- [ ] **Next:** Query data binding dinamis Ruang Talent dari tabel `campaign_applications`, `tasks`, `campaign_kol_contracts`, dan `invoices`.
- [ ] **Next:** CRUD Master KOL (`kols`), Akun Medsos (`kol_platforms`), dan Rate Cards (`kol_rate_cards`).
- [ ] **Next:** CRUD Kategori (`categories`) & Tiers (`tiers`).
- [ ] **Next:** Porting Apify Scraper Engine Service dari CI3 ke CI4.

### Fase 4: Endpoint `/campaign` — Campaign Management
- [x] Dashboard metrik ringkasan kampanye, brief, draft submitted, dan invoice.
- [x] API feed artikel untuk landing page (`ArticlesApi.php`).
- [x] Sidebar dinamis per sub-role (Staff vs Leader vs Admin).
- [ ] **Next:** Modul Master Campaign (CRUD, target budget, brand).
- [ ] **Next:** Modul Briefs & Approval Workflow (history approval, upload guideline).
- [ ] **Next:** Modul Review Draft Video (submission link draft, form feedback revisi).
- [ ] **Next:** Modul Pengiriman Sampel Produk (`kol_shipments`, resi kurir ekspedisi).
- [ ] **Next:** Modul Postings & Engagement Metrics.
- [ ] **Next:** Modul Finance Hub (verifikasi invoices, pencatatan pembayaran).
- [ ] **Next:** Contract Generator (generate PDF perjanjian kerjasama).

### Fase 5: Notifikasi & Automasi
- [ ] Porting WhatsApp notification service untuk reminder deadline draft dan status invoice.
- [ ] Spark commands CI4 untuk penjadwalan berkala (cron job scraper & reminder).
- [ ] Pengujian menyeluruh alur kerja end-to-end antar portal.
