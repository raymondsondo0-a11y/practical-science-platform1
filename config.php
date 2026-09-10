<?php
session_start();
$cfg = __DIR__ . '/hotel-config.php';
if (!file_exists($cfg)) {
    if (basename($_SERVER['PHP_SELF']) !== 'setup.php') { header('Location: setup.php'); exit; }
} else {
    require_once $cfg;
}
?>