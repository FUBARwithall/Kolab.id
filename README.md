# Monorepo KOLAB.ID (CodeIgniter 4)

Monorepo tiga aplikasi web KOLAB.ID berbasis **CodeIgniter 4**, didesain untuk deploy via
**cPanel Git Version Control** (pull-based, tanpa CI server eksternal).

| App | Folder | Domain rencana | Fungsi |
| --- | --- | --- | --- |
| Landing | `landing/` | `kolab.id` | Landing page + blog (CI4, SSR, baca artikel lewat Campaign API — **tanpa akses DB**) |
| Campaign | `campaign/` | `app.kolab.id` | Backend orkestrasi campaign KOL (CI4 enterprise) — sumber REST API `/articles/feed` |
| Datahub | `datahub/` | `datahub.kolab.id` | Card generator/invoice & intelligence hub (CI4) |
| Shared | `shared/uploads/` | — | Aset upload SEMUA app (`campaign/` & `datahub/` namespace). Runtime, tidak di-commit |

---

## Layout di Server cPanel

> [!IMPORTANT]
> Berbeda dari CI3, Document Root web server pada CodeIgniter 4 **wajib mengarah ke subfolder `public/`**.

```text
~/repositories/Kolab.id/            ← clone Git Version Control (BUKAN docroot)
~/Kolab.id/
   ├── landing/
   │       └── public/              ← docroot kolab.id
   ├── campaign/
   │       ├── public/              ← docroot app.kolab.id
   │       │       uploads -> ../../shared/uploads/campaign   (symlink)
   │       └── ...
   ├── datahub/
   │       ├── public/              ← docroot datahub.kolab.id
   │       │       uploads -> ../../shared/uploads/datahub    (symlink)
   │       └── ...
   └── shared/uploads/              ← file upload runtime (775, tidak disentuh deploy)
```

---

## Setup Pertama Kali (cPanel Terminal)

Setelah repository selesai di-clone via Git Version Control di cPanel:

```bash
cd ~/repositories/Kolab.id
bash setup-server.sh
```

Skrip ini secara otomatis:
1. Menyiapkan folder `writable/` (`cache`, `logs`, `session`, `uploads`) dengan permission `775`.
2. Menyiapkan folder penyimpanan permanen `shared/uploads/`.
3. Membuat symlink `uploads` di dalam `public/` per aplikasi.

---

## Development Lokal

| Situs | Port Default | Perintah CLI |
| --- | --- | --- |
| Landing | `8080` (atau `8087`) | `cd landing && php spark serve` |
| Campaign | `8000` | `cd campaign && php spark serve --port 8000` |
| Datahub | `8081` | `cd datahub && php spark serve --port 8081` |

Untuk preview Tailwind CSS pada `landing`:
```bash
cd landing
npm run dev     # watch CSS + spark serve sekaligus
```
