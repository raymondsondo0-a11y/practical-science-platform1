<?php $current = $subject ?: 'Science'; $subjectItems = $subject ? filter_practicals($PRACTICALS, $subject, '', '') : $PRACTICALS; ?>
<section class="page-title"><p class="eyebrow">SUBJECT</p><h1><?= e($current) ?></h1><p>Practical work, calculations, formulas, graphs, analysis and evaluation.</p></section>
<div class="cards">
<?php foreach($subjectItems as $item): ?>
<article class="practical-card"><div class="card-top"><span class="pill"><?= e($item['level']) ?></span><span><?= e($item['topic']) ?></span></div><h2><?= e($item['title']) ?></h2><h3>Aim</h3><p><?= e($item['aim']) ?></p><h3>Formula / calculation</h3><div class="formula"><?= e($item['formula']) ?></div><h3>Graph</h3><p><?= e($item['graph']) ?></p><h3>Safety</h3><p><?= e($item['safety']) ?></p><div class="tags"><?php foreach($item['tags'] as $tag): ?><span>#<?= e($tag) ?></span><?php endforeach; ?></div></article>
<?php endforeach; ?>
</div>
