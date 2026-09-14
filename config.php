<?php
session_start();

$configFile = __DIR__ . '/movie-config.php';
if (!file_exists($configFile)) {
    if (basename($_SERVER['PHP_SELF']) !== 'setup.php') {
        header('Location: setup.php');
        exit;
    }
} else {
    require_once $configFile;
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function is_admin(): bool {
    return !empty($_SESSION['movie_admin']);
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: /admin/');
        exit;
    }
}
?>