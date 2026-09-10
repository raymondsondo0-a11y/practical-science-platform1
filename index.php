<?php
// Practical Science Platform - public entry point
// Works on standard PHP hosting such as InfinityFree.
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/data.php';

$page = $_GET['page'] ?? 'home';
$allowed = ['home', 'practicals', 'subject'];
if (!in_array($page, $allowed, true)) {
    $page = 'home';
}

$subject = $_GET['subject'] ?? '';
$level = $_GET['level'] ?? '';
$search = trim($_GET['q'] ?? '');

$filtered = filter_practicals($PRACTICALS, $subject, $level, $search);

include __DIR__ . '/app/views/header.php';
if ($page === 'subject') {
    include __DIR__ . '/app/views/subject.php';
} elseif ($page === 'practicals') {
    include __DIR__ . '/app/views/practicals.php';
} else {
    include __DIR__ . '/app/views/home.php';
}
include __DIR__ . '/app/views/footer.php';
