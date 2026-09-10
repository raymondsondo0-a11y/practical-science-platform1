<section class="page-title"><p class="eyebrow">PRACTICAL LIBRARY</p><h1>Science practicals</h1><p>Search by topic, subject or level.</p></section>
<form class="filters" method="get" action="index.php">
  <input type="hidden" name="page" value="practicals">
  <input name="q" value="<?= e($search) ?>" placeholder="Search practicals, topics, formulas...">
  <select name="subject"><option value="">All subjects</option><?php foreach(['Biology','Chemistry','Physics'] as $s): ?><option <?= $subject===$s?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select>
  <select name="level"><option value="">All levels</option><option value="O-Level" <?= $level==='O-Level'?'selected':'' ?>>O-Level</option><option value="A-Level" <?= $level==='A-Level'?'selected':'' ?>>A-Level</option></select>
  <button class="button" type="submit">Search</button>
</form>
<div class="cards">
<?php foreach($filtered as $item): ?>
  <article class="practical-card"><div class="card-top"><span class="pill"><?= e($item['subject']) ?></span><span><?= e($item['level']) ?></span></div><h2><?= e($item['title']) ?></h2><p class="muted"><?= e($item['topic']) ?></p><p><?= e($item['aim']) ?></p><div class="formula"><?= e($item['formula']) ?></div><a class="text-link" href="index.php?page=subject&subject=<?= urlencode($item['subject']) ?>">Study <?= e($item['subject']) ?> practicals →</a></article>
<?php endforeach; ?>
</div>
<?php if (!$filtered): ?><div class="empty">No practical matched your search.</div><?php endif; ?>
