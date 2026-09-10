<?php
// Practical Science Platform - single public entry point
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/data.php';

$page = $_GET['page'] ?? 'home';
$allowed = ['home', 'practicals', 'subject', 'practical'];
if (!in_array($page, $allowed, true)) $page = 'home';

$subject = $_GET['subject'] ?? '';
$level = $_GET['level'] ?? '';
$search = trim($_GET['q'] ?? '');
$id = $_GET['id'] ?? '';

$filtered = filter_practicals($PRACTICALS, $subject, $level, $search);
$selectedPractical = null;
foreach ($PRACTICALS as $p) {
    if (($p['id'] ?? '') === $id) { $selectedPractical = $p; break; }
}

include __DIR__ . '/app/views/header.php';
if ($page === 'subject') {
    include __DIR__ . '/app/views/subject.php';
} elseif ($page === 'practicals') {
    include __DIR__ . '/app/views/practicals.php';
} elseif ($page === 'practical' && $selectedPractical) {
    include __DIR__ . '/app/views/practical.php';
} else {
    include __DIR__ . '/app/views/home.php';
}
include __DIR__ . '/app/views/footer.php';
