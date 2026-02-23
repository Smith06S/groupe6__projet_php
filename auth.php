<?php
function start_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function require_login(): void
{
    start_session();

    if (empty($_SESSION['user_id'])) {
        header("Location: /php_exam/groupe6__projet_php/login");
        exit;
    }
}
