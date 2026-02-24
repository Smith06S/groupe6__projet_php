
<!DOCTYPE html>
<html>
<body>

<h1>Détails de l'article</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<?php
if ($result && $result->num_rows > 0) {

     $article = $result->fetch_assoc();
    $connectedUserId = intval($_SESSION['user_id'] ?? 0);
    $canEdit = false;

    if ($connectedUserId > 0) {
        $stmtCurrentUser = $mysqli->prepare("SELECT role FROM user WHERE id = ? LIMIT 1");
        if ($stmtCurrentUser) {
            $stmtCurrentUser->bind_param("i", $connectedUserId);
            $stmtCurrentUser->execute();
            $resCurrentUser = $stmtCurrentUser->get_result();
            $currentUser = $resCurrentUser ? $resCurrentUser->fetch_assoc() : null;
            $stmtCurrentUser->close();

            $isAdmin = !empty($currentUser['role']) && strtolower($currentUser['role']) === 'admin';
            $isOwner = $connectedUserId === intval($article['auteur_id']);
            $canEdit = $isAdmin || $isOwner;
        }
    }

    echo "<div>";
    echo "<p>Nom : " . htmlspecialchars($article['nom']) . "</p>";
    echo "<p>Description : " . htmlspecialchars($article['description']) . "</p>";
    echo "<p>Prix : " . htmlspecialchars($article['prix']) . "</p>";
    echo "<p>Date de publication : " . htmlspecialchars($article['date_publication']) . "</p>";
    echo "<p>Image URL : " . htmlspecialchars($article['image_url']) . "</p>";
    if ($stockQuantityColumn !== null) {
        $stmtStockDisplay = $mysqli->prepare("SELECT `{$stockQuantityColumn}` AS stock_qty FROM stock WHERE article_id = ? LIMIT 1");
        if ($stmtStockDisplay) {
            $stmtStockDisplay->bind_param("i", $id);
            $stmtStockDisplay->execute();
            $resStockDisplay = $stmtStockDisplay->get_result();
            $stockDisplay = $resStockDisplay ? $resStockDisplay->fetch_assoc() : null;
            $currentStock = $stockDisplay ? intval($stockDisplay['stock_qty']) : 0;
            echo "<p>Stock : " . htmlspecialchars((string)$currentStock) . "</p>";
            $stmtStockDisplay->close();
        }
    }
    echo "<hr>";
    echo "</div>";
    ?>

    <form method="POST" action="/php_exam/groupe6__projet_php/detail?id=<?php echo $id; ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="add_to_cart" value="1">

        <label>Quantité :</label><br>
        <input type="number" name="quantity" min="1" value="1" required><br><br>

        <button type="submit">Ajouter au panier</button>
    </form>

    <?php if ($canEdit) : ?>
        <form method="POST" action="/php_exam/groupe6__projet_php/edit">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="open_edit" value="1">
            <button type="submit">Modifier / Supprimer cet article</button>
        </form>
    <?php endif; ?>

    <?php

} else {
    echo "<p>Aucun article trouvé.</p>";
}

$stmt->close();
?>

</body>
</html>