<?php
if (!defined('CART_VIEW_CONTEXT')) {
    if (!defined('CART_SKIP_AUTO_DISPATCH')) {
        define('CART_SKIP_AUTO_DISPATCH', true);
    }
    require_once __DIR__ . '/../../Controleur/cartControleur.php';
    showCartPage();
    exit;
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
            <a href="../products/home.php">
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
            <a href="../products/home.php">Accueil</a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="Cart.php">Panier</a>
                <a href="../account/Account.php">Mon Compte</a>
                <a href="../products/Sell.php">Vendre</a>
                
                <a href="/php_exam/groupe6__projet_php/Controleur/logoutControleur.php" class="logout-btn">Déconnexion</a>
            <?php else: ?>
                <a href="../auth/Login.php" class="login-btn">Connexion</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="cart-container">
        <h1>Mon Panier</h1>

        <?php if (!empty($message)) : ?>
            <div class="alert-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (empty($cartItems)) : ?>
            <div class="empty-cart">
                <p>Ton panier est actuellement vide.</p>
                <a href="../products/home.php" class="btn-primary">Découvrir nos vinyles</a>
            </div>
        <?php else : ?>
            <div class="cart-wrapper">
                <div class="cart-items">
                    <?php foreach ($cartItems as $item) : ?>
                        <div class="cart-item-card">
                            <div class="cart-item-image">
                                <img src="<?php echo htmlspecialchars($item['image_url'] ?? ''); ?>" alt="vinyle">
                            </div>
                            
                            <div class="cart-item-details">
                                <h3><?php echo htmlspecialchars($item['nom'] ?? ''); ?></h3>
                                <p class="unit-price">Prix unitaire : <strong><?php echo number_format(floatval($item['prix'] ?? 0), 2, ',', ' '); ?> €</strong></p>
                                
                                <div class="cart-item-actions">
                                    <form method="POST" action="" class="qty-form">
                                        <input type="hidden" name="article_id" value="<?php echo intval($item['article_id'] ?? 0); ?>">
                                        <input type="hidden" name="action" value="set_quantity">
                                        <label>Qté :</label>
                                        <input type="number" name="quantity" min="1" max="<?php echo intval($item['max_quantity'] ?? 10); ?>" value="<?php echo intval($item['quantity'] ?? 1); ?>">
                                        <button type="submit" class="btn-update">OK</button>
                                    </form>

                                    <form method="POST" action="" onsubmit="return confirm('Supprimer cet article ?');">
                                        <input type="hidden" name="article_id" value="<?php echo intval($item['article_id'] ?? 0); ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="btn-remove-link">Supprimer</button>
                                    </form>
                                </div>
                            </div>

                            <div class="cart-item-subtotal">
                                <p>Sous-total</p>
                                <span class="subtotal-price"><?php echo number_format(floatval($item['subtotal'] ?? 0), 2, ',', ' '); ?> €</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <aside class="cart-summary">
                    <div class="summary-card">
                        <h2>Récapitulatif</h2>
                        <div class="summary-line">
                            <span>Nombre d'articles</span>
                            <span><?php echo count($cartItems); ?></span>
                        </div>
                        <div class="summary-line total">
                            <span>Total TTC</span>
                            <span><?php echo number_format(floatval($total ?? 0), 2, ',', ' '); ?> €</span>
                        </div>
                        <a href="Validate.php" class="btn-primary checkout-btn">Passer à la validation</a>
                        <a href="../products/home.php" class="continue-shopping">Continuer mes achats</a>
                    </div>
                </aside>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 - VNYL</p>
    </footer>
    
</body>
</html>