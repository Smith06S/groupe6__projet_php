<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

require_login();

$message = "";

$sessionUserId = intval($_SESSION['user_id'] ?? 0);
$auteurId = $sessionUserId;

function getStockQuantityColumn(mysqli $mysqli): ?string {
    $result = $mysqli->query("SHOW COLUMNS FROM stock");
    if (!$result) {
        return null;
    }

    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = strtolower($row['Field']);
    }

    $candidates = ['quantity', 'quantite', 'stock', 'nombre_stock', 'nb_stock', 'nombre'];
    foreach ($candidates as $candidate) {
        if (in_array($candidate, $columns, true)) {
            return $candidate;
        }
    }

    return null;
}

$stockQuantityColumn = getStockQuantityColumn($mysqli);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);
    $date_publication = trim($_POST['date_publication'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $stockQuantity = intval($_POST['stock_quantity'] ?? 0);

    if ($auteurId <= 0) {
        $auteurId = intval($_POST['auteur_id'] ?? 0);
    }

    if ($nom === '' || $description === '' || $prix < 0 || $date_publication === '' || $auteurId <= 0 || $stockQuantity < 0) {
        $message = "Merci de remplir correctement tous les champs obligatoires.";
    } elseif ($stockQuantityColumn === null) {
        $message = "Table stock introuvable ou colonne de quantité non reconnue.";
    } else {
        $mysqli->begin_transaction();

        try {
            $stmtInsert = $mysqli->prepare("INSERT INTO article (nom, description, prix, date_publication, image_url, auteur_id) VALUES (?, ?, ?, ?, ?, ?)");

            if ($stmtInsert === false) {
                throw new Exception("Erreur SQL (prepare insert article) : " . $mysqli->error);
            }

            $stmtInsert->bind_param("ssdssi", $nom, $description, $prix, $date_publication, $image_url, $auteurId);

            if (!$stmtInsert->execute()) {
                throw new Exception("Erreur création article : " . $stmtInsert->error);
            }

            $articleId = intval($stmtInsert->insert_id);
            $stmtInsert->close();

            $stmtStock = $mysqli->prepare("INSERT INTO stock (article_id, `{$stockQuantityColumn}`) VALUES (?, ?)");
            if ($stmtStock === false) {
                throw new Exception("Erreur SQL (prepare insert stock) : " . $mysqli->error);
            }

            $stmtStock->bind_param("ii", $articleId, $stockQuantity);
            if (!$stmtStock->execute()) {
                throw new Exception("Erreur création stock : " . $stmtStock->error);
            }

            $stmtStock->close();
            $mysqli->commit();
            $message = "Article créé avec succès.";
        } catch (Exception $e) {
            $mysqli->rollback();
            $message = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<body>

<h1>Créer un nouvel article</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<form method="POST" action="Vente.php">
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