<?php
require_once dirname(__DIR__, 2) . "/auth.php";
require_once dirname(__DIR__, 2) . "/db.php";

require_login();

$userId = intval($_SESSION['user_id']);
$message = "";

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
    $action = $_POST['action'] ?? '';
    $articleId = intval($_POST['article_id'] ?? 0);

    if ($articleId > 0) {
        if ($action === 'increase') {
            if ($stockQuantityColumn === null) {
                $message = "Table stock introuvable ou colonne de quantité non reconnue.";
            } else {
                $stmtStock = $mysqli->prepare("SELECT `{$stockQuantityColumn}` AS stock_qty FROM stock WHERE article_id = ? LIMIT 1");
                $stockQty = 0;
                if ($stmtStock) {
                    $stmtStock->bind_param("i", $articleId);
                    $stmtStock->execute();
                    $resStock = $stmtStock->get_result();
                    $rowStock = $resStock ? $resStock->fetch_assoc() : null;
                    $stockQty = intval($rowStock['stock_qty'] ?? 0);
                    $stmtStock->close();
                }

                $stmtCurrentQty = $mysqli->prepare("SELECT COUNT(*) AS qty FROM cart WHERE user_id = ? AND article_id = ?");
                $currentQty = 0;
                if ($stmtCurrentQty) {
                    $stmtCurrentQty->bind_param("ii", $userId, $articleId);
                    $stmtCurrentQty->execute();
                    $resCurrentQty = $stmtCurrentQty->get_result();
                    $rowCurrent = $resCurrentQty ? $resCurrentQty->fetch_assoc() : null;
                    $currentQty = intval($rowCurrent['qty'] ?? 0);
                    $stmtCurrentQty->close();
                }

                if ($stockQty <= $currentQty) {
                    $message = "Stock insuffisant.";
                } else {
                    $stmt = $mysqli->prepare("INSERT INTO cart (user_id, article_id) VALUES (?, ?)");
                    if ($stmt) {
                        $stmt->bind_param("ii", $userId, $articleId);
                        if ($stmt->execute()) {
                            $message = "Quantité augmentée.";
                        } else {
                            $message = "Erreur lors de l'ajout : " . $stmt->error;
                        }
                        $stmt->close();
                    }
                }
            }
        }

        if ($action === 'decrease') {
            $stmt = $mysqli->prepare("DELETE FROM cart WHERE id = (
                                    SELECT id_to_delete FROM (
                                        SELECT id AS id_to_delete
                                        FROM cart
                                        WHERE user_id = ? AND article_id = ?
                                        ORDER BY id ASC
                                        LIMIT 1
                                    ) x
                                  )");
            if ($stmt) {
                $stmt->bind_param("ii", $userId, $articleId);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $message = "Quantité diminuée.";
                } else {
                    $message = "Aucun article à retirer.";
                }
                $stmt->close();
            }
        }

        if ($action === 'remove_all') {
            $stmt = $mysqli->prepare("DELETE FROM cart WHERE user_id = ? AND article_id = ?");
            if ($stmt) {
                $stmt->bind_param("ii", $userId, $articleId);
                if ($stmt->execute()) {
                    $message = "Article supprimé du panier.";
                } else {
                    $message = "Erreur de suppression : " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

$cartItems = [];
$total = 0;

$stmtItems = $mysqli->prepare("SELECT a.id AS article_id,
                                     a.nom,
                                     a.prix,
                                     a.image_url,
                                     COUNT(*) AS quantity,
                                     SUM(a.prix) AS subtotal
                              FROM cart c
                              INNER JOIN article a ON a.id = c.article_id
                              WHERE c.user_id = ?
                              GROUP BY a.id, a.nom, a.prix, a.image_url
                              ORDER BY a.date_publication DESC");

if ($stmtItems) {
    $stmtItems->bind_param("i", $userId);
    $stmtItems->execute();
    $resultItems = $stmtItems->get_result();
    if ($resultItems) {
        $cartItems = $resultItems->fetch_all(MYSQLI_ASSOC);
    }
    $stmtItems->close();
}

foreach ($cartItems as $item) {
    $total += floatval($item['subtotal']);
}
?>
<!DOCTYPE html>
<html>
<body>
<h1>Panier</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<?php if (empty($cartItems)) : ?>
    <p>Ton panier est vide.</p>
<?php else : ?>
    <?php foreach ($cartItems as $item) : ?>
        <div>
            <p><strong><?php echo htmlspecialchars($item['nom']); ?></strong></p>
            <p>Prix unitaire : <?php echo htmlspecialchars($item['prix']); ?></p>
            <p>Quantité : <?php echo intval($item['quantity']); ?></p>
            <p>Sous-total : <?php echo htmlspecialchars($item['subtotal']); ?></p>
            <p>Image : <?php echo htmlspecialchars($item['image_url']); ?></p>

            <form method="POST" action="Panier.php" style="display:inline-block;">
                <input type="hidden" name="article_id" value="<?php echo intval($item['article_id']); ?>">
                <input type="hidden" name="action" value="increase">
                <button type="submit">+1</button>
            </form>

            <form method="POST" action="Panier.php" style="display:inline-block;">
                <input type="hidden" name="article_id" value="<?php echo intval($item['article_id']); ?>">
                <input type="hidden" name="action" value="decrease">
                <button type="submit">-1</button>
            </form>

            <form method="POST" action="Panier.php" style="display:inline-block;">
                <input type="hidden" name="article_id" value="<?php echo intval($item['article_id']); ?>">
                <input type="hidden" name="action" value="remove_all">
                <button type="submit">Supprimer</button>
            </form>
            <hr>
        </div>
    <?php endforeach; ?>

    <h3>Total : <?php echo htmlspecialchars(number_format($total, 2, '.', '')); ?></h3>
    <a href="Confirmation.php">Passer la commande</a>
<?php endif; ?>
</body>
</html>
