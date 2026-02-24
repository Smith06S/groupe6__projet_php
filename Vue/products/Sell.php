<?php
if (!defined('SELL_VIEW_CONTEXT')) {
    require_once __DIR__ . '/../../Controleur/productControleur.php';
    sellProduct();
    exit;
}
?>
<!DOCTYPE html>
<html>
<body>

<h1>Créer un nouvel article</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Nom :</label><br>
    <input type="text" name="nom" required><br><br>

    <label>Description :</label><br>
    <textarea name="description" rows="4" cols="50" required></textarea><br><br>

    <label>Prix :</label><br>
    <input type="number" name="prix" step="0.01" min="0" required><br><br>

    <label>Image URL :</label><br>
    <input type="text" name="image_url"><br><br>

    <label>Quantité à vendre :</label><br>
    <input type="number" name="stock_quantity" min="0" required><br><br>

    <button type="submit">Publier / Mettre en stock</button>
</form>

<p>
    <a href="/php_exam/groupe6__projet_php/Vue/products/Home.php">Retour à la home</a>
</p>

</body>
</html>
