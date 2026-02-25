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
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $password = password_hash($passwordRaw, PASSWORD_BCRYPT);

    if ($username === '' || $mail === '' || $passwordRaw === '' || $passwordConfirm === '') {
        return "Merci de remplir tous les champs.";
    }

    if ($passwordRaw !== $passwordConfirm) {
        return "Les deux mots de passe ne correspondent pas.";
    }

    return registerIdentifiant($username, $mail, $password);
}

