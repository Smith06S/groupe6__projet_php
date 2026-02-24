<?php

$basePath = '/php_exam/groupe6__projet_php';

if (!isset($_GET['action'])) {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
    $relativePath = $requestPath;

    if (str_starts_with($requestPath, $basePath)) {
        $relativePath = substr($requestPath, strlen($basePath));
    }

    $relativePath = trim($relativePath, '/');

    if ($relativePath === '') {
        $_GET['action'] = 'home';
    } else {
        $segments = explode('/', $relativePath);
        $first = $segments[0] ?? 'home';

        if ($relativePath === 'cart/validate') {
            $_GET['action'] = 'validate_cart';
        } elseif ($first === 'edit') {
            $_GET['action'] = 'edit_article';
        } else {
            $_GET['action'] = $first;
        }
    }
}

require __DIR__ . '/route/index.php';