<?php
if (!defined('EDIT_VIEW_CONTEXT')) {
    require_once __DIR__ . '/../../Controleur/productControleur.php';
    editProduct();
    exit;
}
?>
<!DOCTYPE html>
<html>
<body>

<h1>Modifier l'article</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<form method="POST" action="">
    <input type="hidden" name="id" value="<?php echo intval($id); ?>">
    <input type="hidden" name="action" value="update">

    <label>Nom :</label><br>
    <input type="text" name="nom" required value="<?php echo htmlspecialchars($article['nom'] ?? ''); ?>"><br><br>

    <label>Description :</label><br>
    <textarea name="description" rows="4" cols="50" required><?php echo htmlspecialchars($article['description'] ?? ''); ?></textarea><br><br>

    <label>Prix :</label><br>
    <input type="number" name="prix" step="0.01" min="0" required value="<?php echo htmlspecialchars($article['prix'] ?? '0'); ?>"><br><br>

    <label>Image URL :</label><br>
    <input type="text" name="image_url" value="<?php echo htmlspecialchars($article['image_url'] ?? ''); ?>"><br><br>

    <button type="submit">Modifier</button>
</form>

<form method="POST" action="" onsubmit="return confirm('Supprimer cet article ?');">
    <input type="hidden" name="id" value="<?php echo intval($id); ?>">
    <input type="hidden" name="action" value="delete">
    <button type="submit">Supprimer</button>
</form>

<p>
    <a href="/php_exam/groupe6__projet_php/Vue/products/Detail.php?id=<?php echo intval($id); ?>">Retour au détail</a>
</p>

</body>
</html>
