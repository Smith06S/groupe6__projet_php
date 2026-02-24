<!DOCTYPE html>
<html>
<body>

<h1>Créer un nouvel article</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<form method="POST" action="/php_exam/groupe6__projet_php/sell">
    <label>Nom :</label><br>
    <input type="text" name="nom" required><br><br>

    <label>Description :</label><br>
    <textarea name="description" rows="4" cols="50" required></textarea><br><br>

    <label>Prix :</label><br>
    <input type="number" name="prix" step="0.01" min="0" required><br><br>

    <label>Date de publication :</label><br>
    <input type="date" name="date_publication" required><br><br>

    <label>Image URL :</label><br>
    <input type="text" name="image_url"><br><br>

    <label>Stock initial :</label><br>
    <input type="number" name="stock_quantity" min="0" required><br><br>

    <?php if ($sessionUserId > 0) : ?>
        <p>Auteur ID (connecté) : <?php echo intval($sessionUserId); ?></p>
    <?php else : ?>
        <label>Auteur ID :</label><br>
        <input type="number" name="auteur_id" min="1" required><br><br>
    <?php endif; ?>

    <button type="submit">Créer l'article</button>
</form>

</body>
</html>