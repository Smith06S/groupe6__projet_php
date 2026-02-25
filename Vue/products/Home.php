<?php
require_once __DIR__ . '/../../Controleur/productControleur.php';
$articles = showProductsHome();
$searchTerm = trim($_GET['search'] ?? '');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$isAdminUser = false;
$connectedUserId = intval($_SESSION['user_id'] ?? 0);
if ($connectedUserId > 0) {
    $connectedUser = getUserById($connectedUserId);
    $isAdminUser = !empty($connectedUser['role']) && strtolower((string)$connectedUser['role']) === 'admin';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>VNYL - Accueil</title>
    <link rel="stylesheet" href="../../style.css"> 
</head>
<body>
    <header>
        <div class="logo">
            <a href="home.php">
                <svg viewBox="0 0 200 60" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <radialGradient id="grad" cx="50%" cy="50%" r="50%">
                            <stop offset="70%" stop-color="#111"/><stop offset="100%" stop-color="#333"/>
                        </radialGradient>
                    </defs>
                    <circle cx="30" cy="30" r="28" fill="url(#grad)" />
                    <circle cx="30" cy="30" r="17" fill="none" stroke="#fff" stroke-width="0.5" opacity="0.4" />
                    <circle cx="30" cy="30" r="8" fill="#e63946" />
                    <circle cx="30" cy="30" r="1.5" fill="#fff" />
                    <text x="70" y="42" font-family="Arial, sans-serif" font-weight="900" font-size="28" fill="#111">VNYL</text>
                </svg>
            </a>
        </div>
        <nav>
            <a href="home.php">Accueil</a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="../cart/Cart.php">Panier</a>
                <a href="../account/Account.php">Mon Compte</a>
                <a href="Sell.php">Vendre</a>
                
                <?php 
                if($isAdminUser): ?>
                    <a href="../admin/Admin.php">Admin</a>
                <?php endif; ?>
                
                <a href="/php_exam/groupe6__projet_php/Controleur/logoutControleur.php" class="logout-btn">Déconnexion</a>
            <?php else: ?>
                <a href="../auth/Login.php" class="login-btn">Connexion</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <h1>Liste des articles</h1>

        <div class="search-container">
            <form method="GET" action="">
                <input type="text" id="search" name="search" 
                    value="<?= htmlspecialchars($searchTerm) ?>" 
                    placeholder="Rechercher un album, un artiste...">
                <button type="submit">Rechercher</button>
                <?php if ($searchTerm !== '') : ?>
                    <a href="home.php" class="btn-reset">Réinitialiser</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="product-grid">
            <?php if ($articles !== "Error"): ?>
                <?php foreach ($articles as $article): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="<?= htmlspecialchars($article['nom']) ?>">
                        </div>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($article['nom']) ?></h3>
                            <p class="price"><?= htmlspecialchars($article['prix']) ?> €</p>
                            <p>
                                <a href="/php_exam/groupe6__projet_php/Vue/products/Detail.php?id=<?= intval($article['id']) ?>" class="view-detail">
                                    Voir l'article
                                </a>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>
                    <?= $searchTerm !== '' ? 'Aucun article trouvé pour votre recherche.' : 'Aucun article trouvé.' ?>
                </p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 - VNYL</p>
    </footer>
</body>
</html>