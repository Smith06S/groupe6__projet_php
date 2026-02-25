<?php
if (!defined('SELL_VIEW_CONTEXT')) {
    require_once __DIR__ . '/../../Controleur/productControleur.php';
    sellProduct();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>VNYL - Vente</title>
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

    <main class="auth-container">
        <div class="auth-box sell-box">
            <h1>Mettre en vente</h1>

            <?php if (!empty($message)) : ?>
                <p class="alert-message"><strong><?php echo htmlspecialchars($message); ?></strong></p>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="input-group">
                    <label>Nom du vinyle</label>
                    <input type="text" name="nom" placeholder="Ex: Thriller - Michael Jackson" required>
                </div>

                <div class="input-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" placeholder="Décrivez l'état du disque, l'édition..." required></textarea>
                </div>

                <div class="admin-inline-group">
                    <div class="input-group">
                        <label>Prix (€)</label>
                        <input type="number" name="prix" step="0.01" min="0" placeholder="0.00" required>
                    </div>
                    <div class="input-group">
                        <label>Quantité</label>
                        <input type="number" name="stock_quantity" min="1" value="1" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>URL de l'image</label>
                    <input type="text" name="image_url" placeholder="https://lien-vers-l-image">
                </div>

                <button type="submit" class="btn-primary">Publier l'article</button>
            </form>

            <p class="auth-footer">
                <a href="home.php">← Retour à l'accueil</a>
            </p>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 - VNYL</p>
    </footer>

</body>
</html>
