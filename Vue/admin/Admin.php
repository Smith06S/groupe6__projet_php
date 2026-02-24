<?php
if (!defined('ADMIN_VIEW_CONTEXT')) {
    require_once __DIR__ . '/../../Controleur/adminControleur.php';
    exit;
}
?>
<!DOCTYPE html>
<html>
<body>

<h1>Tableau Administrateur</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<h2>Articles</h2>
<?php if (empty($articles)) : ?>
    <p>Aucun article.</p>
<?php else : ?>
    <?php foreach ($articles as $article) : ?>
        <div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_article">
                <input type="hidden" name="article_id" value="<?php echo intval($article['id']); ?>">

                <p><strong>Article #<?php echo intval($article['id']); ?></strong> (Auteur: <?php echo htmlspecialchars($article['auteur_username'] ?? 'N/A'); ?>)</p>

                <label>Nom :</label><br>
                <input type="text" name="nom" required value="<?php echo htmlspecialchars($article['nom'] ?? ''); ?>"><br><br>

                <label>Description :</label><br>
                <textarea name="description" rows="3" cols="50" required><?php echo htmlspecialchars($article['description'] ?? ''); ?></textarea><br><br>

                <label>Prix :</label><br>
                <input type="number" name="prix" step="0.01" min="0" required value="<?php echo htmlspecialchars($article['prix'] ?? '0'); ?>"><br><br>

                <label>Image URL :</label><br>
                <input type="text" name="image_url" value="<?php echo htmlspecialchars($article['image_url'] ?? ''); ?>"><br><br>

                <button type="submit">Modifier article</button>
            </form>

            <form method="POST" action="" onsubmit="return confirm('Supprimer cet article ?');">
                <input type="hidden" name="action" value="delete_article">
                <input type="hidden" name="article_id" value="<?php echo intval($article['id']); ?>">
                <button type="submit">Supprimer article</button>
            </form>
            <hr>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<h2>Utilisateurs</h2>
<?php if (empty($users)) : ?>
    <p>Aucun utilisateur.</p>
<?php else : ?>
    <?php foreach ($users as $u) : ?>
        <div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="target_user_id" value="<?php echo intval($u['id']); ?>">

                <p><strong>User #<?php echo intval($u['id']); ?></strong></p>

                <label>Username :</label><br>
                <input type="text" name="username" required value="<?php echo htmlspecialchars($u['username'] ?? ''); ?>"><br><br>

                <label>Mail :</label><br>
                <input type="email" name="mail" required value="<?php echo htmlspecialchars($u['mail'] ?? ''); ?>"><br><br>

                <label>Role :</label><br>
                <select name="role">
                    <option value="user" <?php echo (strtolower((string)($u['role'] ?? '')) === 'user') ? 'selected' : ''; ?>>user</option>
                    <option value="admin" <?php echo (strtolower((string)($u['role'] ?? '')) === 'admin') ? 'selected' : ''; ?>>admin</option>
                </select><br><br>

                <label>Solde :</label><br>
                <input type="number" name="solde" step="0.01" min="0" required value="<?php echo htmlspecialchars($u['solde'] ?? '0'); ?>"><br><br>

                <label>Photo profil :</label><br>
                <input type="text" name="photo_profil" value="<?php echo htmlspecialchars($u['photo_profil'] ?? ''); ?>"><br><br>

                <button type="submit">Modifier utilisateur</button>
            </form>

            <form method="POST" action="" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="target_user_id" value="<?php echo intval($u['id']); ?>">
                <button type="submit">Supprimer utilisateur</button>
            </form>
            <hr>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
