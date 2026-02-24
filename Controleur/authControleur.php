<?php
require_once __DIR__ . '/../Modele/userModele.php';

function connection(){
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return null;
    }

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    return VerifyIdentifiant($username, $password);
    
}



function register(){
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return null;
    }

    $username = trim($_POST['username'] ?? '');
    $mail = trim($_POST['mail'] ?? '');
    $passwordRaw = $_POST['password'] ?? '';
    $password = password_hash($passwordRaw, PASSWORD_BCRYPT);

    if ($username === '' || $mail === '' || $passwordRaw === '') {
        return "Merci de remplir tous les champs.";
    }

    return registerIdentifiant($username, $mail, $password);
}

