<?php
require_once __DIR__ . '/../Modele/articleModele.php';
require_once __DIR__ . '/../Modele/userModele.php';

function showProductsHome() {
	$searchTerm = trim($_GET['search'] ?? '');

    if ($searchTerm !== '') {
        $articles = searchArticlesByTermOrderedByDate($searchTerm);
    } else {
	    $articles = getAllArticlesOrderedByDate();
    }

    if ($articles === false) {
        die("Erreur lors de la récupération des articles.");
    }
    if (empty($articles)) {
        return "Error";
    }
    return $articles;

}

function detailProduct() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $message = '';
    $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);

    if ($id <= 0) {
        die("ID d'article invalide.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
        $userId = intval($_SESSION['user_id'] ?? 0);

        if ($userId <= 0) {
            header("Location: /php_exam/groupe6__projet_php/Vue/auth/Login.php");
            exit;
        }

        $quantity = intval($_POST['quantity'] ?? 1);
        if ($quantity < 1) {
            $quantity = 1;
        }

        $availableStock = getArticleAvailableStockById($id);

        if ($availableStock === 0) {
            $message = "Article en rupture de stock.";
        } elseif ($availableStock > 0 && $quantity > $availableStock) {
            $message = "Stock insuffisant. Disponible : " . max(0, $availableStock);
        } else {
            $ok = addArticleToCart($userId, $id, $quantity);
            $message = $ok ? "Article ajouté au panier." : "Erreur lors de l'ajout au panier.";
        }
    }

    $article = getArticleById($id);
    if (!$article) {
        die("Aucun article trouvé.");
    }

    $currentStock = getArticleAvailableStockById($id);
    $connectedUserId = intval($_SESSION['user_id'] ?? 0);
    $canEdit = false;

    if ($connectedUserId > 0) {
        $currentUser = getUserById($connectedUserId);
        $isAdmin = !empty($currentUser['role']) && strtolower($currentUser['role']) === 'admin';
        $isOwner = $connectedUserId === intval($article['auteur_id']);
        $canEdit = $isAdmin || $isOwner;
    }

    if (!defined('DETAIL_VIEW_CONTEXT')) {
        define('DETAIL_VIEW_CONTEXT', true);
    }

    require __DIR__ . '/../Vue/products/Detail.php';
}

function sellProduct() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $userId = intval($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) {
        header("Location: /php_exam/groupe6__projet_php/Vue/auth/Login.php");
        exit;
    }

    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $prix = floatval($_POST['prix'] ?? -1);
        $imageUrl = trim($_POST['image_url'] ?? '');
        $stockQuantity = intval($_POST['stock_quantity'] ?? -1);

        if ($nom === '' || $description === '' || $prix < 0 || $stockQuantity < 0) {
            $message = 'Merci de remplir correctement tous les champs obligatoires.';
        } else {
            $existingArticleId = findArticleByNameDifferentAuthor($nom, $userId);
            $newArticleId = createArticle($nom, $description, $prix, $imageUrl, $userId);

            if ($newArticleId <= 0) {
                $message = "Erreur lors de la création de l'article.";
            } else {
                if ($existingArticleId !== null) {
                    $okStock = incrementStockForArticle($existingArticleId, $stockQuantity);
                    $message = $okStock
                        ? 'Article ajouté pour ce vendeur et stock global incrémenté.'
                        : 'Article créé, mais erreur lors de la mise à jour du stock.';
                } else {
                    $okStock = insertStockForArticle($newArticleId, $stockQuantity);
                    $message = $okStock
                        ? 'Article créé avec stock initial.'
                        : 'Article créé, mais erreur lors de la création du stock.';
                }
            }
        }
    }

    if (!defined('SELL_VIEW_CONTEXT')) {
        define('SELL_VIEW_CONTEXT', true);
    }

    require __DIR__ . '/../Vue/products/Sell.php';
}

function editProduct() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $currentUserId = intval($_SESSION['user_id'] ?? 0);
    if ($currentUserId <= 0) {
        header("Location: /php_exam/groupe6__projet_php/Vue/auth/Login.php");
        exit;
    }

    $message = '';
    $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);

    if ($id <= 0) {
        die("ID d'article invalide.");
    }

    $article = getArticleById($id);
    if (!$article) {
        die('Article introuvable.');
    }

    $currentUser = getUserById($currentUserId);
    $isAdmin = !empty($currentUser['role']) && strtolower($currentUser['role']) === 'admin';
    $isOwner = intval($article['auteur_id']) === $currentUserId;

    if (!$isAdmin && !$isOwner) {
        die('Accès refusé : vous ne pouvez modifier que vos articles.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? 'update';

        if ($action === 'update') {
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $prix = floatval($_POST['prix'] ?? -1);
            $imageUrl = trim($_POST['image_url'] ?? '');

            if ($nom === '' || $description === '' || $prix < 0) {
                $message = 'Merci de remplir correctement tous les champs obligatoires.';
            } else {
                $updated = updateArticleById($id, $nom, $description, $prix, $imageUrl);
                $message = $updated ? 'Article modifié avec succès.' : 'Erreur lors de la modification.';
            }
        }

        if ($action === 'delete') {
            $deleted = deleteArticleCascadeById($id);
            if ($deleted) {
                header('Location: /php_exam/groupe6__projet_php/Vue/products/Home.php');
                exit;
            }
            $message = 'Erreur lors de la suppression de l\'article.';
        }

        $article = getArticleById($id);
        if (!$article && $action !== 'delete') {
            die('Article introuvable.');
        }
    }

    if (!defined('EDIT_VIEW_CONTEXT')) {
        define('EDIT_VIEW_CONTEXT', true);
    }

    require __DIR__ . '/../Vue/products/Edit.php';
}