<?php
declare(strict_types=1);

const APP_NAME = 'Practical Science Platform';
const APP_VERSION = '1.0.0';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function filter_practicals(array $items, string $subject = '', string $level = '', string $search = ''): array {
    return array_values(array_filter($items, static function (array $item) use ($subject, $level, $search): bool {
        if ($subject !== '' && strcasecmp($item['subject'], $subject) !== 0) return false;
        if ($level !== '' && strcasecmp($item['level'], $level) !== 0) return false;
        if ($search !== '') {
            $haystack = strtolower($item['title'].' '.$item['topic'].' '.$item['subject'].' '.$item['level'].' '.implode(' ', $item['tags']));
            if (strpos($haystack, strtolower($search)) === false) return false;
        }
        return true;
    }));
}
