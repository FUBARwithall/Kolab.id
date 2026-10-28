<?php
/**
 * Kartu artikel. Variabel: $item (row artikel dari API).
 */
$slug   = is_object($item) ? $item->slug : $item['slug'];
$title  = is_object($item) ? $item->title : $item['title'];
$excerpt= is_object($item) ? ($item->excerpt ?? '') : ($item['excerpt'] ?? '');
$cover  = is_object($item) ? ($item->cover_image_url ?? null) : ($item['cover_image_url'] ?? null);
$cat    = is_object($item) ? ($item->category ?? 'Artikel') : ($item['category'] ?? 'Artikel');
$date   = is_object($item) ? ($item->published_at ?? $item->published_at_label ?? '') : ($item['published_at'] ?? $item['published_at_label'] ?? '');
$author = is_object($item) ? ($item->author_name ?? $item->author ?? 'Tim Kolab') : ($item['author_name'] ?? $item['author'] ?? 'Tim Kolab');

if (!empty($date) && strtotime($date)) {
    $date = date('d M Y', strtotime($date));
}

$href = base_url('artikel/' . rawurlencode($slug));
?>
<a href="<?= $href ?>" class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-[0px_4px_6px_-1px_rgba(0,0,0,0.1),0px_2px_4px_-2px_rgba(0,0,0,0.1)] transition duration-300 hover:-translate-y-1 hover:shadow-[0px_18px_30px_-12px_rgba(23,43,77,0.25)]">
  <div class="block aspect-[16/9] overflow-hidden bg-slate-100">
    <?php if ($cover): ?>
      <img src="<?= esc($cover) ?>" alt="<?= esc($title) ?>" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
    <?php else: ?>
      <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#dbe1ff] via-[#eff4ff] to-[#d3e4fe]">
        <span class="text-xl font-bold text-primary">KOLAB.ID</span>
      </div>
    <?php endif; ?>
  </div>
  <div class="flex flex-1 flex-col p-6 sm:p-8">
    <span class="text-[11px] font-bold uppercase leading-[14px] tracking-[0.55px] text-brand"><?= esc($cat) ?></span>
    <h3 class="mt-3 font-display text-[20px] font-bold leading-[28px] tracking-[-0.01em] text-ink transition duration-300 group-hover:text-brand">
      <?= esc($title) ?>
    </h3>
    <p class="mt-2 line-clamp-3 text-sm leading-[24px] tracking-[-0.005em] text-body"><?= esc((string) $excerpt) ?></p>
    <div class="mt-auto pt-6">
      <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-2 border-t border-[rgba(195,198,215,0.3)] pt-4 text-[11px] font-semibold uppercase leading-[14px] tracking-[0.44px]">
        <span class="text-brand"><?= esc($author) ?></span>
        <span class="text-[#565e74]"><?= esc($date) ?></span>
      </div>
    </div>
  </div>
</a>
