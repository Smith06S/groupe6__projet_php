<?php
if (!defined('DETAIL_VIEW_CONTEXT')) {
    require_once __DIR__ . '/../../Controleur/productControleur.php';
    detailProduct();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>VNYL - Detail</title>
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
                
                <a href="/php_exam/groupe6__projet_php/Controleur/logoutControleur.php" class="logout-btn">Déconnexion</a>
            <?php else: ?>
                <a href="../auth/Login.php" class="login-btn">Connexion</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="detail-container">
        <?php if (!empty($message)) : ?>
            <div class="alert-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="product-detail-wrapper">
            <div class="product-detail-image">
                <img src="<?php echo htmlspecialchars($article['image_url'] ?? ''); ?>" alt="vinyle">
            </div>

            <div class="product-detail-info">
                <div class="info-header">
                    <p class="publish-date">Publié le : <?php echo date('d/m/Y', strtotime($article['date_publication'])); ?></p>
                    <h1><?php echo htmlspecialchars($article['nom'] ?? ''); ?></h1>
                    <p class="vendeur-link">Vendu par : 
                        <a href="/php_exam/groupe6__projet_php/Vue/account/Account.php?id=<?php echo intval($article['auteur_id'] ?? 0); ?>">
                            <strong><?php echo htmlspecialchars($article['auteur_username'] ?? 'Vendeur VNYL'); ?></strong>
                        </a>
                    </p>
                </div>

                <p class="detail-price"><?php echo number_format(floatval($article['prix'] ?? 0), 2, ',', ' '); ?> €</p>
                
                <div class="detail-description">
                    <h3>Description</h3>
                    <p><?php echo htmlspecialchars($article['description'] ?? ''); ?></p>
                </div>

                <div class="detail-stock">
                    <span class="stock-badge <?php echo (intval($currentStock) > 0) ? 'in-stock' : 'out-of-stock'; ?>">
                        Stock : <?php echo max(0, intval($currentStock)); ?> exemplaires
                    </span>
                </div>

                <form method="POST" action="" class="add-to-cart-form">
                    <input type="hidden" name="id" value="<?php echo intval($id); ?>">
                    <input type="hidden" name="add_to_cart" value="1">

                    <div class="qty-selector">
                        <label>Quantité :</label>
                        <input type="number" name="quantity" min="1" max="<?php echo max(0, intval($currentStock ?? 0)); ?>" value="1" <?php echo (intval($currentStock) <= 0) ? 'disabled' : ''; ?> required>
                    </div>

                    <button type="submit" class="btn-primary" <?php echo (intval($currentStock) <= 0) ? 'disabled' : ''; ?>>
                        <?php echo (intval($currentStock) > 0) ? 'Ajouter au panier' : 'Rupture de stock'; ?>
                    </button>
                </form>

                <?php if (!empty($canEdit)) : ?>
                    <div class="admin-actions-link">
                        <a href="/php_exam/groupe6__projet_php/Vue/products/Edit.php?id=<?php echo intval($id); ?>" class="btn-secondary">
                            Modifier cet article
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 - VNYL</p>
    </footer>

</body>
</html>
