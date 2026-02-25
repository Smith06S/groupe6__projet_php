<?php
require_once __DIR__ . '/../../Controleur/authControleur.php';
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = register();
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
            <?php if(isset($_SESSION['user'])): ?>
                <a href="../cart/index.php">Panier</a>
                <a href="../account/Account.php">Mon Compte</a>
                <a href="../products/Sell.php">Vendre</a>
                <?php if($_SESSION['user']['role'] === 'admin'): ?>
                    <a href="../admin/Admin.php">Admin</a>
                <?php endif; ?>
                <a href="index.php?action=logout" class="logout-btn">Déconnexion</a>
            <?php else: ?>
                <a href="Login.php" class="login-btn">Connexion</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="auth-container">
        <div class="auth-box">
            <h1>Inscription</h1>

            <?php if (!empty($message)) : ?>
                <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <label>Nom d'utilisateur :</label>
                    <input type="text" name="username" placeholder="Choisissez un pseudo" required>
                </div>

                <div class="input-group">
                    <label>Adresse Mail :</label>
                    <input type="email" name="mail" placeholder="votre@email.com" required>
                </div>

                <div class="input-group">
                    <label>Mot de passe :</label>
                    <input type="password" name="password" placeholder="Mot de passe sécurisé" required>
                </div>

                <div class="input-group">
                    <label>Confirmer Password :</label>
                    <input type="password" name="password_confirm" required>
                </div>

                <button type="submit" class="btn-primary">Créer mon compte</button>
            </form>

            <p class="auth-footer">
                Déjà inscrit ? <a href="Login.php">Connectez-vous ici</a>
            </p>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 - VNYL</p>
    </footer>

</body>
</html>