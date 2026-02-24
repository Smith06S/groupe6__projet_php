<?php


function auth_register() : void
{
    $message = $_SESSION['error'] ?? '';
    unset($_SESSION['error']);

    require_once __DIR__ . '/../Vue/auth/Register.php';
}

function auth_logout() : void
{
    session_destroy();
    header('Location: /php_exam/groupe6__projet_php/login');
    exit;
}

function auth_login() : void
{
    if (isset($_SESSION['user_id'])) {
        header('Location: /php_exam/groupe6__projet_php/home');
        exit;
    }

    $message = $_SESSION['error'] ?? '';
    unset($_SESSION['error']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $_SESSION['error'] = "Veuillez remplir tous les champs.";
            header('Location: /php_exam/groupe6__projet_php/login');
            exit;
        }

        $_SESSION['error'] = "Authentification DB non implémentée dans ce contrôleur.";
        header('Location: /php_exam/groupe6__projet_php/login');
        exit;
    }

    require_once __DIR__ . '/../Vue/auth/Login.php';
}