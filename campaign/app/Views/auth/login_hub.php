<section class="relative overflow-hidden bg-[#f8fafc] py-16 sm:py-24">
  <!-- Ambient background glow matching landing hero -->
  <div class="pointer-events-none absolute -top-40 left-1/2 -z-10 h-[500px] w-[1000px] -translate-x-1/2 rounded-full bg-gradient-to-tr from-blue-100/60 via-indigo-50/40 to-transparent blur-3xl" aria-hidden="true"></div>

  <div class="mx-auto max-w-[1240px] px-5 sm:px-8 lg:px-16">

    <!-- Header Section -->
    <div class="mx-auto max-w-3xl text-center">
      <div class="inline-flex items-center gap-2 rounded-full border border-blue-200/80 bg-blue-50/80 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-brand">
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        Pintu Gerbang Portal Terpadu
      </div>

      <h1 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-ink sm:text-4xl lg:text-5xl">
        Pilih Portal Masuk ke Sistem
      </h1>
      <p class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-body sm:text-lg">
        Setiap sistem memiliki lingkungan kerja, hak akses, dan form login mandiri. Silakan klik tombol di bawah untuk diarahkan ke form login portal yang Anda tuju.
      </p>
    </div>

    <!-- Alert / Flash Messages -->
    <?php if (session()->getFlashdata('error')): ?>
      <div class="mx-auto mt-8 max-w-2xl rounded-2xl border border-red-200 bg-red-50/90 p-4 text-sm text-red-800 shadow-sm" role="alert">
        <div class="flex items-start gap-3">
          <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="font-medium leading-relaxed"><?= esc(session()->getFlashdata('error')) ?></div>
        </div>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
      <div class="mx-auto mt-8 max-w-2xl rounded-2xl border border-emerald-200 bg-emerald-50/90 p-4 text-sm text-emerald-800 shadow-sm" role="alert">
        <div class="flex items-start gap-3">
          <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="font-medium leading-relaxed"><?= esc(session()->getFlashdata('success')) ?></div>
        </div>
      </div>
    <?php endif; ?>

    <!-- 2 PORTAL CARDS (HANYA BUTTON KE FORM LOGIN MASING-MASING) -->
    <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 max-w-4xl mx-auto">

      <!-- ================= CARD 1: CAMPAIGN MANAGEMENT ================= -->
      <div onclick="window.location.href='<?= root_url('campaign/login') ?>'"
           class="group relative flex flex-col justify-between rounded-3xl border border-slate-200/90 bg-white p-8 shadow-[0_4px_25px_-4px_rgba(0,0,0,0.05)] transition-all duration-300 hover:-translate-y-1 hover:border-blue-500/40 hover:shadow-[0_20px_35px_-10px_rgba(37,99,235,0.12)] cursor-pointer">
        <div class="absolute inset-x-8 top-0 h-1.5 rounded-b-full bg-gradient-to-r from-blue-600 to-indigo-600"></div>

        <div>
          <!-- Top badge and icon -->
          <div class="flex items-center justify-between">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition duration-300 group-hover:scale-110">
              <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
              </svg>
            </div>
            <span class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-blue-700">
              /campaign
            </span>
          </div>

          <!-- Card Header info -->
          <h2 class="mt-6 font-display text-2xl font-bold text-ink">
            Campaign Management
          </h2>
          <p class="mt-2.5 text-sm leading-relaxed text-body">
            Sistem terintegrasi untuk Brand &amp; Tim Internal: orkestrasi brief, review draft video, pengiriman sampel, postingan live, dan invoice.
          </p>

          <!-- Module Highlights -->
          <ul class="mt-6 space-y-2.5 text-xs text-slate-600">
            <li class="flex items-center gap-2">
              <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span>Orkestrasi Campaign &amp; Timeline</span>
            </li>
            <li class="flex items-center gap-2">
              <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span>Approval Draft Video &amp; Revisi Real-time</span>
            </li>
            <li class="flex items-center gap-2">
              <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span>Finance, Escrow &amp; Invoice Pajak</span>
            </li>
          </ul>
        </div>

        <!-- Button Redirect ke Form Login Campaign -->
        <div class="mt-8 pt-6 border-t border-slate-100">
          <a href="<?= root_url('campaign/login') ?>"
             class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-3.5 text-sm font-semibold text-white shadow-[0_2px_10px_-2px_rgba(37,99,235,0.4)] transition hover:bg-blue-700 hover:shadow-[0_4px_16px_-2px_rgba(37,99,235,0.5)]">
            <span>Buka Login Campaign</span>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
          </a>
          <p class="mt-2 text-center text-[11px] text-slate-400">Hak Akses: Administrator, Leader &amp; Staff</p>
        </div>
      </div>

      <!-- ================= CARD 2: KOL DATA HUB ================= -->
      <div onclick="window.location.href='<?= root_url('datahub/login') ?>'"
           class="group relative flex flex-col justify-between rounded-3xl border border-slate-200/90 bg-white p-8 shadow-[0_4px_25px_-4px_rgba(0,0,0,0.05)] transition-all duration-300 hover:-translate-y-1 hover:border-emerald-500/40 hover:shadow-[0_20px_35px_-10px_rgba(16,185,129,0.12)] cursor-pointer">
        <div class="absolute inset-x-8 top-0 h-1.5 rounded-b-full bg-gradient-to-r from-emerald-500 to-teal-500"></div>

        <div>
          <!-- Top badge and icon -->
          <div class="flex items-center justify-between">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 transition duration-300 group-hover:scale-110">
              <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
              </svg>
            </div>
            <span class="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-700">
              /datahub
            </span>
          </div>

          <!-- Card Header info -->
          <h2 class="mt-6 font-display text-2xl font-bold text-ink">
            KOL Data Hub
          </h2>
          <p class="mt-2.5 text-sm leading-relaxed text-body">
            Pusat data kecerdasan kreator: master database 10.000+ KOL, analisis followers, scraper Apify otomatis, rate card, dan tiering.
          </p>

          <!-- Module Highlights -->
          <ul class="mt-6 space-y-2.5 text-xs text-slate-600">
            <li class="flex items-center gap-2">
              <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span>Master Profil KOL Multi-Platform</span>
            </li>
            <li class="flex items-center gap-2">
              <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span>Mesin Apify Scraper Real-Time</span>
            </li>
            <li class="flex items-center gap-2">
              <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span>Standar Rate Card &amp; Tiering KOL</span>
            </li>
          </ul>
        </div>

        <!-- Button Redirect ke Form Login Datahub -->
        <div class="mt-8 pt-6 border-t border-slate-100">
          <a href="<?= root_url('datahub/login') ?>"
             class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3.5 text-sm font-semibold text-white shadow-[0_2px_10px_-2px_rgba(16,185,129,0.4)] transition hover:bg-emerald-700 hover:shadow-[0_4px_16px_-2px_rgba(16,185,129,0.5)]">
            <span>Buka Login Datahub</span>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
          </a>
          <p class="mt-2 text-center text-[11px] text-slate-400">Hak Akses: Superadmin &amp; Talent KOL</p>
        </div>
      </div>

      

    </div>

    <!-- Features Banner Under Cards -->
    <div class="mt-16 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8">
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div class="flex items-start gap-4">
          <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div>
            <h3 class="font-display text-sm font-bold text-ink">Form Login Mandiri</h3>
            <p class="mt-1 text-xs leading-5 text-body">Masing-masing sistem memiliki halaman login tersendiri berbasis tema AdminLTE.</p>
          </div>
        </div>

        <div class="flex items-start gap-4">
          <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <div>
            <h3 class="font-display text-sm font-bold text-ink">Keamanan Terisolasi</h3>
            <p class="mt-1 text-xs leading-5 text-body">Role dienkripsi dan diproteksi ketat sehingga kreator tidak dapat masuk ke sistem brand.</p>
          </div>
        </div>

        <div class="flex items-start gap-4">
          <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="font-display text-sm font-bold text-ink">Sidebar Eksklusif per Portal</h3>
            <p class="mt-1 text-xs leading-5 text-body">Setelah masuk, Anda disajikan sidebar relevan tanpa distraksi modul lain.</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>
