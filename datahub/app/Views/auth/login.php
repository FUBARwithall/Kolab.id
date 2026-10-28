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
        Single Sign-On Terpadu
      </div>

      <h1 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-ink sm:text-4xl lg:text-5xl">
        Pilih Portal Masuk ke Sistem
      </h1>
      <p class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-body sm:text-lg">
        Satu akun untuk setiap ekosistem. Silakan pilih portal kerja Anda di bawah ini untuk mengelola campaign, datahub analitik KOL, atau komisi kreator.
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

    <!-- 3 PORTAL CARDS GRID -->
    <div class="mt-12 grid grid-cols-1 gap-8 lg:grid-cols-3">

      <!-- ================= CARD 1: CAMPAIGN MANAGEMENT ================= -->
      <div class="group relative flex flex-col justify-between rounded-3xl border border-slate-200/90 bg-white p-7 shadow-[0_4px_25px_-4px_rgba(0,0,0,0.05)] transition-all duration-300 hover:-translate-y-1 hover:border-blue-500/40 hover:shadow-[0_20px_35px_-10px_rgba(37,99,235,0.12)]">
        <div class="absolute inset-x-8 top-0 h-1.5 rounded-b-full bg-gradient-to-r from-blue-600 to-indigo-600"></div>

        <div>
          <!-- Top badge and icon -->
          <div class="flex items-center justify-between">
            <div class="flex h-13 w-13 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
              </svg>
            </div>
            <span class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-blue-700">
              /campaign
            </span>
          </div>

          <!-- Card Header info -->
          <h2 class="mt-5 font-display text-xl font-bold text-ink">
            Campaign Management
          </h2>
          <p class="mt-2 text-sm leading-relaxed text-body">
            Untuk Brand &amp; Tim Internal: kelola brief, approval draft video, shipment sampel produk, dan invoice brand.
          </p>

          <!-- Login Form -->
          <form action="<?= base_url('login') ?>" method="POST" class="mt-6 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="portal" value="campaign">

            <div>
              <label for="email_campaign" class="block text-xs font-bold uppercase tracking-wider text-slate-500">
                Email Internal
              </label>
              <input type="email" name="email" id="email_campaign" required placeholder="admin@kolab.id"
                     class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-ink placeholder:text-slate-400 transition focus:border-blue-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-600/10">
            </div>

            <div>
              <label for="password_campaign" class="block text-xs font-bold uppercase tracking-wider text-slate-500">
                Kata Sandi
              </label>
              <input type="password" name="password" id="password_campaign" required placeholder="••••••••"
                     class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-ink placeholder:text-slate-400 transition focus:border-blue-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-600/10">
            </div>

            <button type="submit"
                    class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-3 text-sm font-semibold text-white shadow-[0_2px_10px_-2px_rgba(37,99,235,0.4)] transition hover:bg-blue-700 hover:shadow-[0_4px_16px_-2px_rgba(37,99,235,0.5)]">
              <span>Masuk ke Campaign</span>
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
              </svg>
            </button>
          </form>
        </div>

        <!-- Quick Demo Footer -->
        <div class="mt-6 border-t border-slate-100 pt-4">
          <button type="button" onclick="fillForm('campaign', 'admin@kolab.id', 'admin123')"
                  class="flex w-full items-center justify-between rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-3.5 py-2 text-xs font-medium text-slate-600 transition hover:border-blue-300 hover:bg-blue-50/50 hover:text-blue-700">
            <span class="flex items-center gap-1.5">
              <svg class="h-3.5 w-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"/>
              </svg>
              <span>Quick Demo: <strong>Admin</strong></span>
            </span>
            <span class="rounded bg-white px-1.5 py-0.5 text-[10px] font-semibold text-slate-400">admin123</span>
          </button>
        </div>
      </div>

      <!-- ================= CARD 2: KOL DATA HUB ================= -->
      <div class="group relative flex flex-col justify-between rounded-3xl border border-slate-200/90 bg-white p-7 shadow-[0_4px_25px_-4px_rgba(0,0,0,0.05)] transition-all duration-300 hover:-translate-y-1 hover:border-emerald-500/40 hover:shadow-[0_20px_35px_-10px_rgba(16,185,129,0.12)]">
        <div class="absolute inset-x-8 top-0 h-1.5 rounded-b-full bg-gradient-to-r from-emerald-500 to-teal-500"></div>

        <div>
          <!-- Top badge and icon -->
          <div class="flex items-center justify-between">
            <div class="flex h-13 w-13 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
              </svg>
            </div>
            <span class="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-700">
              /datahub
            </span>
          </div>

          <!-- Card Header info -->
          <h2 class="mt-5 font-display text-xl font-bold text-ink">
            KOL Data Hub
          </h2>
          <p class="mt-2 text-sm leading-relaxed text-body">
            Master database KOL Indonesia, metrik followers, engagement rate riil, scraper engine, rate card, dan tiers.
          </p>

          <!-- Login Form -->
          <form action="<?= base_url('login') ?>" method="POST" class="mt-6 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="portal" value="datahub">

            <div>
              <label for="email_datahub" class="block text-xs font-bold uppercase tracking-wider text-slate-500">
                Email Specialist
              </label>
              <input type="email" name="email" id="email_datahub" required placeholder="leader@kolab.id"
                     class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-ink placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-600/10">
            </div>

            <div>
              <label for="password_datahub" class="block text-xs font-bold uppercase tracking-wider text-slate-500">
                Kata Sandi
              </label>
              <input type="password" name="password" id="password_datahub" required placeholder="••••••••"
                     class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-ink placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-600/10">
            </div>

            <button type="submit"
                    class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3 text-sm font-semibold text-white shadow-[0_2px_10px_-2px_rgba(16,185,129,0.4)] transition hover:bg-emerald-700 hover:shadow-[0_4px_16px_-2px_rgba(16,185,129,0.5)]">
              <span>Masuk ke Datahub</span>
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
              </svg>
            </button>
          </form>
        </div>

        <!-- Quick Demo Footer -->
        <div class="mt-6 border-t border-slate-100 pt-4">
          <button type="button" onclick="fillForm('datahub', 'leader@kolab.id', 'leader123')"
                  class="flex w-full items-center justify-between rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-3.5 py-2 text-xs font-medium text-slate-600 transition hover:border-emerald-300 hover:bg-emerald-50/50 hover:text-emerald-700">
            <span class="flex items-center gap-1.5">
              <svg class="h-3.5 w-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"/>
              </svg>
              <span>Quick Demo: <strong>Leader</strong></span>
            </span>
            <span class="rounded bg-white px-1.5 py-0.5 text-[10px] font-semibold text-slate-400">leader123</span>
          </button>
        </div>
      </div>

      <!-- ================= CARD 3: PORTAL CREATOR (KOL) ================= -->
      <div class="group relative flex flex-col justify-between rounded-3xl border border-slate-200/90 bg-white p-7 shadow-[0_4px_25px_-4px_rgba(0,0,0,0.05)] transition-all duration-300 hover:-translate-y-1 hover:border-purple-500/40 hover:shadow-[0_20px_35px_-10px_rgba(147,51,234,0.12)]">
        <div class="absolute inset-x-8 top-0 h-1.5 rounded-b-full bg-gradient-to-r from-purple-600 to-pink-600"></div>

        <div>
          <!-- Top badge and icon -->
          <div class="flex items-center justify-between">
            <div class="flex h-13 w-13 items-center justify-center rounded-2xl bg-purple-50 text-purple-600">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
            <span class="rounded-full border border-purple-100 bg-purple-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-purple-700">
              /KOL
            </span>
          </div>

          <!-- Card Header info -->
          <h2 class="mt-5 font-display text-xl font-bold text-ink">
            Portal Creator
          </h2>
          <p class="mt-2 text-sm leading-relaxed text-body">
            Khusus Influencer &amp; Kreator: unggah preview draft video konten, tanda tangan kontrak, &amp; pantau pembayaran.
          </p>

          <!-- Login Form -->
          <form action="<?= base_url('login') ?>" method="POST" class="mt-6 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="portal" value="kol">

            <div>
              <label for="email_kol" class="block text-xs font-bold uppercase tracking-wider text-slate-500">
                Email Kreator
              </label>
              <input type="email" name="email" id="email_kol" required placeholder="kol@kolab.id"
                     class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-ink placeholder:text-slate-400 transition focus:border-purple-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-purple-600/10">
            </div>

            <div>
              <label for="password_kol" class="block text-xs font-bold uppercase tracking-wider text-slate-500">
                Kata Sandi
              </label>
              <input type="password" name="password" id="password_kol" required placeholder="••••••••"
                     class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-ink placeholder:text-slate-400 transition focus:border-purple-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-purple-600/10">
            </div>

            <button type="submit"
                    class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-purple-600 py-3 text-sm font-semibold text-white shadow-[0_2px_10px_-2px_rgba(147,51,234,0.4)] transition hover:bg-purple-700 hover:shadow-[0_4px_16px_-2px_rgba(147,51,234,0.5)]">
              <span>Masuk ke Portal KOL</span>
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
              </svg>
            </button>
          </form>
        </div>

        <!-- Quick Demo Footer -->
        <div class="mt-6 border-t border-slate-100 pt-4">
          <button type="button" onclick="fillForm('kol', 'kol@kolab.id', 'kol123')"
                  class="flex w-full items-center justify-between rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-3.5 py-2 text-xs font-medium text-slate-600 transition hover:border-purple-300 hover:bg-purple-50/50 hover:text-purple-700">
            <span class="flex items-center gap-1.5">
              <svg class="h-3.5 w-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"/>
              </svg>
              <span>Quick Demo: <strong>KOL</strong></span>
            </span>
            <span class="rounded bg-white px-1.5 py-0.5 text-[10px] font-semibold text-slate-400">kol123</span>
          </button>
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
            <h3 class="font-display text-sm font-bold text-ink">Isolasi Akses &amp; Keamanan</h3>
            <p class="mt-1 text-xs leading-5 text-body">Tiap portal memiliki boundary terpisah dengan enkripsi sesi ketat.</p>
          </div>
        </div>

        <div class="flex items-start gap-4">
          <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <div>
            <h3 class="font-display text-sm font-bold text-ink">Satu Akun Terintegrasi</h3>
            <p class="mt-1 text-xs leading-5 text-body">Kredensial tersentralisasi di bawah database enterprise kolab_ci4.</p>
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
            <p class="mt-1 text-xs leading-5 text-body">Fokus kerja tanpa distraksi menu antar sistem yang tidak relevan.</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<script>
function fillForm(portal, email, pass) {
  var emailEl = document.getElementById('email_' + portal);
  var passEl = document.getElementById('password_' + portal);
  if (emailEl && passEl) {
    emailEl.value = email;
    passEl.value = pass;
    emailEl.focus();
  }
}
</script>
