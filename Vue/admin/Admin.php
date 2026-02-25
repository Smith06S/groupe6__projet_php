<?php
if (!defined('ADMIN_VIEW_CONTEXT')) {
    require_once __DIR__ . '/../../Controleur/adminControleur.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>VNYL - Admin</title>
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
                <a href="../account/Account.php">Mon Compte</a>
                <a href="../products/Sell.php">Vendre</a>
                
                <a href="/php_exam/groupe6__projet_php/Controleur/logoutControleur.php" class="logout-btn">Déconnexion</a>
            <?php else: ?>
                <a href="../auth/Login.php" class="login-btn">Connexion</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="admin-container">
        <h1>Tableau Administrateur</h1>

        <?php if (!empty($message)) : ?>
            <div class="alert-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <section class="admin-section">
            <h2>Gestion des Articles</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID / Auteur</th>
                            <th>Détails du produit</th>
                            <th>Prix & Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article) : ?>
                        <tr>
                            <td>
                                <span class="admin-badge">#<?php echo intval($article['id']); ?></span>
                                <p class="admin-subtitle"><?php echo htmlspecialchars($article['auteur_username'] ?? 'N/A'); ?></p>
                            </td>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_article">
                                <input type="hidden" name="article_id" value="<?php echo intval($article['id']); ?>">
                                <td>
                                    <input type="text" name="nom" class="admin-input" value="<?php echo htmlspecialchars($article['nom'] ?? ''); ?>" required>
                                    <textarea name="description" class="admin-input small-text" rows="2"><?php echo htmlspecialchars($article['description'] ?? ''); ?></textarea>
                                </td>
                                <td>
                                    <div class="admin-inline-group">
                                        <input type="number" name="prix" class="admin-input small" step="0.01" value="<?php echo htmlspecialchars($article['prix'] ?? '0'); ?>">
                                        <span class="currency">€</span>
                                    </div>
                                    <input type="text" name="image_url" class="admin-input" value="<?php echo htmlspecialchars($article['image_url'] ?? ''); ?>" placeholder="URL de l'image">
                                </td>
                                <td class="admin-actions">
                                    <button type="submit" class="btn-edit">Sauvegarder</button>
                            </form>
                                    <form method="POST" action="" onsubmit="return confirm('Supprimer cet article ?');">
                                        <input type="hidden" name="action" value="delete_article">
                                        <input type="hidden" name="article_id" value="<?php echo intval($article['id']); ?>">
                                        <button type="submit" class="btn-delete">Supprimer</button>
                                    </form>
                                </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-section user-section">
            <h2>Gestion des Utilisateurs</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th>Rôle & Portefeuille</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u) : ?>
                        <tr>
                            <td><span class="admin-badge gray">#<?php echo intval($u['id']); ?></span></td>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_user">
                                <input type="hidden" name="target_user_id" value="<?php echo intval($u['id']); ?>">
                                <td>
                                    <input type="text" name="username" class="admin-input" value="<?php echo htmlspecialchars($u['username'] ?? ''); ?>">
                                    <input type="email" name="mail" class="admin-input small-text" value="<?php echo htmlspecialchars($u['mail'] ?? ''); ?>">
                                </td>
                                <td>
                                    <select name="role" class="admin-input">
                                        <option value="user" <?php echo (strtolower((string)($u['role'] ?? '')) === 'user') ? 'selected' : ''; ?>>User</option>
                                        <option value="admin" <?php echo (strtolower((string)($u['role'] ?? '')) === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <input type="number" name="solde" class="admin-input" step="0.01" value="<?php echo htmlspecialchars($u['solde'] ?? '0'); ?>">
                                </td>
                                <td class="admin-actions">
                                    <button type="submit" class="btn-edit">Mettre à jour</button>
                            </form>
                                    <form method="POST" action="" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="target_user_id" value="<?php echo intval($u['id']); ?>">
                                        <button type="submit" class="btn-delete">Bannir</button>
                                    </form>
                                </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 - VNYL</p>
    </footer>

</body>
</html>
