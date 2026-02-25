<?php
if (!defined('CART_VALIDATE_VIEW_CONTEXT')) {
    if (!defined('CART_SKIP_AUTO_DISPATCH')) {
        define('CART_SKIP_AUTO_DISPATCH', true);
    }
    require_once __DIR__ . '/../../Controleur/cartControleur.php';
    showCartValidatePage();
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

    <main class="validate-container">
        <div class="validate-header">
            <h1>Validation de commande</h1>
            <p class="balance-status">
                Solde actuel : <strong><?php echo number_format(floatval($user['solde'] ?? 0), 2, ',', ' '); ?> €</strong>
            </p>
        </div>

        <?php if (!empty($message)) : ?>
            <div class="alert-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="validate-wrapper">
            <div class="order-summary-box">
                <section class="validate-card">
                    <h2>Votre commande</h2>
                    <div class="mini-cart-list">
                        <?php foreach ($cartItems as $item) : ?>
                            <div class="mini-cart-item">
                                <div class="mini-item-info">
                                    <p class="name"><?php echo htmlspecialchars($item['nom'] ?? ''); ?></p>
                                    <p class="meta">Quantité : <?php echo intval($item['quantity'] ?? 0); ?></p>
                                </div>
                                <span class="subtotal"><?php echo number_format(floatval($item['subtotal'] ?? 0), 2, ',', ' '); ?> €</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="validate-total">
                        <span>Total à régler</span>
                        <strong><?php echo number_format(floatval($total ?? 0), 2, ',', ' '); ?> €</strong>
                    </div>

                    <?php if (!empty($isBalanceTooLow)) : ?>
                        <div class="low-balance-alert">
                            <p>Solde insuffisant pour valider la commande.</p>
                            <a href="../account/Account.php" class="btn-small">Ajouter de l'argent</a>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            <div class="billing-form-box">
                <section class="validate-card">
                    <h2>Informations de facturation</h2>
                    <?php if (empty($isBalanceTooLow)) : ?>
                        <form method="POST" action="" class="billing-form">
                            <input type="hidden" name="validate_order" value="1">

                            <div class="input-group">
                                <label>Adresse de livraison</label>
                                <input type="text" name="facturation_address" placeholder="123 Rue du Vinyle" required>
                            </div>

                            <div class="form-row">
                                <div class="input-group">
                                    <label>Ville</label>
                                    <input type="text" name="facturation_city" placeholder="Paris" required>
                                </div>
                                <div class="input-group">
                                    <label>Code postal</label>
                                    <input type="text" name="facturation_zip" placeholder="75000" required>
                                </div>
                            </div>

                            <button type="submit" class="btn-primary">Confirmer l'achat</button>
                        </form>
                    <?php else : ?>
                        <p class="empty-msg">Veuillez recharger votre compte pour accéder au formulaire de paiement.</p>
                    <?php endif; ?>
                    
                    <a href="Cart.php" class="continue-shopping">← Retour au panier</a>
                </section>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 - VNYL</p>
    </footer>

</body>
</html>