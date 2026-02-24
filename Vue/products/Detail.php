<?php
if (!defined('DETAIL_VIEW_CONTEXT')) {
    require_once __DIR__ . '/../../Controleur/productControleur.php';
    detailProduct();
    exit;
}
?>
<!DOCTYPE html>
<html>
<body>

<h1>Détails de l'article</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<div>
    <p>Nom : <?php echo htmlspecialchars($article['nom'] ?? ''); ?></p>
    <p>Description : <?php echo htmlspecialchars($article['description'] ?? ''); ?></p>
    <p>Prix : <?php echo htmlspecialchars($article['prix'] ?? ''); ?></p>
    <p>Date de publication : <?php echo htmlspecialchars($article['date_publication'] ?? ''); ?></p>
    <p>Image URL : <?php echo htmlspecialchars($article['image_url'] ?? ''); ?></p>
    <p>Stock : <?php echo htmlspecialchars((string)max(0, intval($currentStock ?? 0))); ?></p>
    <hr>
</div>

<form method="POST" action="">
    <input type="hidden" name="id" value="<?php echo intval($id); ?>">
    <input type="hidden" name="add_to_cart" value="1">

    <label>Quantité :</label><br>
    <input type="number" name="quantity" min="1" max="<?php echo max(0, intval($currentStock ?? 0)); ?>" value="1" <?php echo (intval($currentStock ?? 0) <= 0) ? 'disabled' : ''; ?> required><br><br>

    <button type="submit" <?php echo (intval($currentStock ?? 0) <= 0) ? 'disabled' : ''; ?>>Ajouter au panier</button>
</form>

<?php if (!empty($canEdit)) : ?>
    <p>
        <a href="/php_exam/groupe6__projet_php/Vue/products/Edit.php?id=<?php echo intval($id); ?>">
            Modifier / Supprimer cet article
        </a>
    </p>
<?php endif; ?>

</body>
</html>
