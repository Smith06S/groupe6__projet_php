<?php
require_once __DIR__ . '/../Modele/userModele.php';

function require_login()
{
    if (!isset($_SESSION['user_id']) || intval($_SESSION['user_id']) <= 0) {
        header('Location: /php_exam/groupe6__projet_php/Vue/auth/Login.php');
        exit;
    }
}

function loadAccountPage()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    require_login();

    $message = '';
    $connectedUserId = intval($_SESSION['user_id']);
    $targetUserId = intval($_GET['id'] ?? $_POST['id'] ?? $connectedUserId);

    if ($targetUserId <= 0) {
        die('Utilisateur invalide.');
    }

    $isOwnProfile = ($targetUserId === $connectedUserId);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if (!$isOwnProfile) {
            $message = "Vous ne pouvez pas modifier le compte d'un autre utilisateur.";
        } elseif ($action === 'update_profile') {
            $username = trim($_POST['username'] ?? '');
            $mail = trim($_POST['mail'] ?? '');
            $photo_profil = trim($_POST['photo_profil'] ?? '');
            $newPassword = trim($_POST['new_password'] ?? '');

            if ($username === '' || $mail === '') {
                $message = 'Username et mail sont obligatoires.';
            } elseif (checkUnique($username, $mail, $connectedUserId)) {
                $message = 'Username ou email déjà utilisé.';
            } else {
                $hashedPassword = $newPassword !== '' ? password_hash($newPassword, PASSWORD_BCRYPT) : null;
                $updated = userUpdate($username, $mail, $photo_profil, $connectedUserId, $hashedPassword);

                if ($updated) {
                    $_SESSION['username'] = $username;
                    $message = 'Informations utilisateur mises a jour.';
                } else {
                    $message = 'Erreur lors de la mise a jour.';
                }
            }
        } elseif ($action === 'add_money') {
            $amount = floatval($_POST['amount'] ?? 0);

            if ($amount <= 0) {
                $message = 'Le montant ajoute doit etre superieur a 0.';
            } else {
                $updated = addMoney($amount, $connectedUserId);
                $message = $updated ? 'Solde mis a jour avec succes.' : "Erreur lors de l'ajout d'argent.";
            }
        }
    }

    $user = getUserById($targetUserId);
    if (!$user) {
        die('Utilisateur introuvable.');
    }

    $createdArticles = userArticles($targetUserId);
    $purchasedArticles = $isOwnProfile ? userArticlePurchased($connectedUserId) : [];
    $invoices = $isOwnProfile ? userArticleBought($connectedUserId) : [];

    if (!defined('ACCOUNT_VIEW_CONTEXT')) {
        define('ACCOUNT_VIEW_CONTEXT', true);
    }

    require __DIR__ . '/../Vue/account/Account.php';
}

loadAccountPage();
?>