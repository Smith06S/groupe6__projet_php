<!DOCTYPE html>
<html>
<body>

<h1>Modifier l'article</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<form method="POST" action="/php_exam/groupe6__projet_php/edit">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="action" value="update">

    <label>Nom :</label><br>
    <input type="text" name="nom" required value="<?php echo htmlspecialchars($article['nom']); ?>"><br><br>

    <label>Description :</label><br>
    <textarea name="description" rows="4" cols="50" required><?php echo htmlspecialchars($article['description']); ?></textarea><br><br>

    <label>Prix :</label><br>
    <input type="number" name="prix" step="0.01" min="0" required value="<?php echo htmlspecialchars($article['prix']); ?>"><br><br>

    <label>Date de publication :</label><br>
    <input type="date" name="date_publication" required value="<?php echo htmlspecialchars($dateInputValue); ?>"><br><br>

    <label>Image URL :</label><br>
    <input type="text" name="image_url" value="<?php echo htmlspecialchars($article['image_url']); ?>"><br><br>

    <button type="submit">Modifier</button>
</form>

<form method="POST" action="/php_exam/groupe6__projet_php/edit" onsubmit="return confirm('Supprimer cet article ?');">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="action" value="delete">
    <button type="submit">Supprimer</button>
</form>

</body>
</html>