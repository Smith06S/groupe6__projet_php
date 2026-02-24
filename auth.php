<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: /php_exam/groupe6__projet_php/login');
        exit;
    }
}