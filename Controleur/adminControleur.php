<?php
require_once __DIR__ . '/../Modele/adminModele.php';
require_once __DIR__ . '/../Modele/userModele.php';
require_once __DIR__ . '/../Modele/articleModele.php';

function showAdminPage() {
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$connectedUserId = intval($_SESSION['user_id'] ?? 0);
	if ($connectedUserId <= 0) {
		header('Location: /php_exam/groupe6__projet_php/Vue/auth/Login.php');
		exit;
	}

	$connectedUser = getUserById($connectedUserId);
	$isAdmin = !empty($connectedUser['role']) && strtolower($connectedUser['role']) === 'admin';

	if (!$isAdmin) {
		die('Accès refusé : section réservée aux administrateurs.');
	}

	$message = '';

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$action = $_POST['action'] ?? '';

		if ($action === 'delete_article') {
			$articleId = intval($_POST['article_id'] ?? 0);
			if ($articleId > 0) {
				$ok = adminDeleteArticleById($articleId);
				$message = $ok ? 'Article supprimé.' : 'Erreur lors de la suppression de l\'article.';
			}
		}

		if ($action === 'update_article') {
			$articleId = intval($_POST['article_id'] ?? 0);
			$nom = trim($_POST['nom'] ?? '');
			$description = trim($_POST['description'] ?? '');
			$prix = floatval($_POST['prix'] ?? -1);
			$imageUrl = trim($_POST['image_url'] ?? '');

			if ($articleId > 0 && $nom !== '' && $description !== '' && $prix >= 0) {
				$ok = updateArticleById($articleId, $nom, $description, $prix, $imageUrl);
				$message = $ok ? 'Article mis à jour.' : 'Erreur lors de la mise à jour de l\'article.';
			} else {
				$message = 'Champs article invalides.';
			}
		}

		if ($action === 'update_user') {
			$userId = intval($_POST['target_user_id'] ?? 0);
			$username = trim($_POST['username'] ?? '');
			$mail = trim($_POST['mail'] ?? '');
			$role = strtolower(trim($_POST['role'] ?? 'user'));
			$solde = floatval($_POST['solde'] ?? 0);
			$photoProfil = trim($_POST['photo_profil'] ?? '');

			if ($role !== 'admin') {
				$role = 'user';
			}

			if ($userId === $connectedUserId) {
				$message = 'Impossible de modifier votre propre compte depuis cette section.';
			} elseif ($userId > 0 && $username !== '' && $mail !== '' && $solde >= 0) {
				$ok = adminUpdateUserById($userId, $username, $mail, $role, $solde, $photoProfil);
				$message = $ok ? 'Utilisateur mis à jour.' : 'Erreur lors de la mise à jour utilisateur.';
			} else {
				$message = 'Champs utilisateur invalides.';
			}
		}

		if ($action === 'delete_user') {
			$userId = intval($_POST['target_user_id'] ?? 0);
			if ($userId === $connectedUserId) {
				$message = 'Impossible de supprimer votre propre compte administrateur.';
			} elseif ($userId > 0) {
				$ok = adminDeleteUserById($userId);
				$message = $ok ? 'Utilisateur supprimé.' : 'Erreur lors de la suppression utilisateur.';
			}
		}
	}

	$articles = adminGetAllArticles();
	$users = array_values(array_filter(adminGetAllUsers(), function ($user) use ($connectedUserId) {
		return intval($user['id'] ?? 0) !== $connectedUserId;
	}));

	if (!defined('ADMIN_VIEW_CONTEXT')) {
		define('ADMIN_VIEW_CONTEXT', true);
	}

	require __DIR__ . '/../Vue/admin/Admin.php';
}

showAdminPage();

