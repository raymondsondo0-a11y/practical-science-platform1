<section class="hero">
  <div>
    <p class="eyebrow">SECONDARY & ADVANCED SECONDARY</p>
    <h1>Learn science by doing the practical.</h1>
    <p class="lead">A practical-focused learning platform for Biology, Chemistry and Physics — with procedures, observations, calculations, formulas, graphs, errors and conclusions.</p>
    <a class="button" href="index.php?page=practicals">Explore practicals</a>
  </div>
  <div class="hero-panel"><div class="metric"><b>3</b><span>Science subjects</span></div><div class="metric"><b>2</b><span>Education levels</span></div><div class="metric"><b>∞</b><span>Practical variations</span></div></div>
</section>
<section class="section">
  <div class="section-head"><div><p class="eyebrow">SUBJECTS</p><h2>Choose your science</h2></div></div>
  <div class="subject-grid">
    <?php foreach (['Biology'=>'Life processes, specimens, microscopy, ecology and experiments.','Chemistry'=>'Reactions, titration, qualitative analysis, rates and electrochemistry.','Physics'=>'Measurements, mechanics, electricity, waves, optics and graphs.'] as $name=>$desc): ?>
      <a class="subject-card" href="index.php?page=subject&subject=<?= urlencode($name) ?>"><span class="subject-icon"><?= $name[0] ?></span><h3><?= e($name) ?></h3><p><?= e($desc) ?></p><span class="arrow">Open subject →</span></a>
    <?php endforeach; ?>
  </div>
</section>
<section class="section">
  <div class="section-head"><div><p class="eyebrow">START HERE</p><h2>Core practical skills</h2></div></div>
  <div class="skill-grid">
    <?php foreach (['Experimental design','Variables & controls','Observation & recording','Formula & calculation','Graphs & gradients','Errors & uncertainty','Analysis & inference','Conclusion & evaluation'] as $skill): ?><div class="skill">✓ <?= e($skill) ?></div><?php endforeach; ?>
  </div>
</section>
