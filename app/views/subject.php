<?php $current = $subject ?: 'Science'; $subjectItems = $subject ? filter_practicals($PRACTICALS, $subject, '', '') : $PRACTICALS; ?>
<section class="page-title"><p class="eyebrow">VIRTUAL PRACTICAL LAB</p><h1><?= e($current) ?></h1><p>Choose a practical and enter the laboratory. Equipment, measurements, reactions, calculations and observations are connected in one experience.</p></section>
<div class="cards">
<?php foreach($subjectItems as $item): ?>
<article class="practical-card">
  <div class="card-top"><span class="pill"><?= e($item['level']) ?></span><span><?= e($item['topic']) ?></span></div>
  <h2><?= e($item['title']) ?></h2>
  <p><?= e($item['aim']) ?></p>
  <div class="formula"><?= e($item['formula']) ?></div>
  <div class="tags"><?php foreach($item['tags'] as $tag): ?><span>#<?= e($tag) ?></span><?php endforeach; ?></div>
  <a class="button" href="index.php?page=practical&id=<?= urlencode($item['id']) ?>">Enter practical →</a>
</article>
<?php endforeach; ?>
</div>
