<?php
if (!defined('ACCOUNT_VIEW_CONTEXT')) {
    require_once __DIR__ . '/../../Controleur/accountControleur.php';
    exit;
}

$user = is_array($user) ? $user : [];
$createdArticles = is_array($createdArticles) ? $createdArticles : [];
$purchasedArticles = is_array($purchasedArticles) ? $purchasedArticles : [];
$invoices = is_array($invoices) ? $invoices : [];
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
                <a href="../cart/Cart.php">Panier</a>
                <a href="Account.php">Mon Compte</a>
                <a href="../products/Sell.php">Vendre</a>
                
                <a href="/php_exam/groupe6__projet_php/Controleur/logoutControleur.php" class="logout-btn">Déconnexion</a>
            <?php else: ?>
                <a href="../auth/Login.php" class="login-btn">Connexion</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="account-container">
        <div class="account-header">
            <h1>Mon Compte</h1>
            <p class="balance-badge">Solde : <strong><?php echo number_format(floatval($user['solde'] ?? 0), 2, ',', ' '); ?> €</strong></p>
        </div>

        <div class="account-grid">
            <div class="account-column">
                <section class="account-card">
                    <h2>Mes informations</h2>
                    <div class="product-image">
                        <?php if (!empty($user['photo_profil'])) : ?>
                            <img src="<?php echo htmlspecialchars($user['photo_profil']); ?>" alt="Photo de profil">
                        <?php else : ?>
                            <div class="profile-placeholder">Aucune photo</div>
                        <?php endif; ?>
                    </div>

                    <div class="product-info">
                        <form method="POST" class="account-form">
                            <input type="hidden" name="id" value="<?php echo intval($connectedUserId); ?>">
                            <input type="hidden" name="action" value="update_profile">

                            <div class="input-group">
                                <label>Username (Unique)</label>
                                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                            </div>

                            <div class="input-group">
                                <label>Email (Unique)</label>
                                <input type="email" name="mail" value="<?php echo htmlspecialchars($user['mail'] ?? ''); ?>" required>
                            </div>

                            <div class="input-group">
                                <label>Lien de la photo de profil</label>
                                <input type="text" name="photo_profil" value="<?php echo htmlspecialchars($user['photo_profil'] ?? ''); ?>" placeholder="URL de l'image">
                            </div>

                            <div class="input-group">
                                <label>Nouveau mot de passe</label>
                                <input type="password" name="new_password" placeholder="Laisser vide pour ne pas changer">
                                <input type="password" name="new_password_confirm" placeholder="Confirmer le mot de passe" style="margin-top:10px;">
                            </div>

                            <button type="submit" class="btn-primary">Mettre à jour mon profil</button>
                        </form>
                    </div>
                </section>

                <section class="account-card">
                    <h2>Recharger mon compte</h2>
                    <form method="POST" class="inline-form">
                        <input type="hidden" name="action" value="add_money">
                        <input type="number" name="amount" step="0.01" min="0.01" placeholder="Montant à ajouter" required>
                        <button type="submit" class="btn-small">Ajouter</button>
                    </form>
                </section>
            </div>

            <div class="account-column">
                
                <section class="account-card">
                    <h2>Mes articles en vente</h2>
                    <div class="item-list">
                        <?php if (!empty($createdArticles)) : ?>
                            <?php foreach ($createdArticles as $article) : ?>
                                <div class="mini-item">
                                    <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="vinyle">
                                    <div class="mini-item-info">
                                        <p class="name"><?php echo htmlspecialchars($article['nom']); ?></p>
                                        <p class="price"><?php echo htmlspecialchars($article['prix']); ?> €</p>
                                    </div>
                                    <a href="../products/Edit.php?id=<?php echo $article['id']; ?>" class="btn-icon">✎</a>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p class="empty-msg">Vous n'avez aucun article en vente.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="account-card">
                    <h2>Mes achats</h2>
                    <div class="item-list">
                        <?php if (!empty($purchasedArticles)) : ?>
                            <?php foreach ($purchasedArticles as $article) : ?>
                                <div class="mini-item purchased">
                                    <div class="mini-item-info">
                                        <p class="name"><?php echo htmlspecialchars($article['nom']); ?></p>
                                        <p class="meta">Acheté le : <?php echo htmlspecialchars($article['transaction_date']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p class="empty-msg">Vous n'avez pas encore effectué d'achats.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="account-card">
                    <h2>Mes factures</h2>
                    <div class="item-list">
                        <?php if (!empty($invoices)) : ?>
                            <?php foreach ($invoices as $invoice) : ?>
                                <div class="mini-item invoice">
                                    <div>
                                        <p class="name">Commande #<?php echo intval($invoice['id']); ?></p>
                                        <p class="meta"><?php echo htmlspecialchars($invoice['transaction_date']); ?> • <?php echo htmlspecialchars($invoice['montant']); ?> €</p>
                                    </div>
                                    <button class="btn-small outline">Détails</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p class="empty-msg">Aucune facture disponible.</p>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 - VNYL</p>
    </footer>

</body>
</html>