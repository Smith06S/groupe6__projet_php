<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

start_session();

$message = "";
$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    die("ID d'article invalide.");
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $userId = intval($_SESSION['user_id'] ?? 0);

    if ($userId <= 0) {
        header("Location: /php_exam/groupe6__projet_php/login");
        exit;
    }

    $quantity = intval($_POST['quantity'] ?? 1);
    if ($quantity < 1) {
        $quantity = 1;
    }

    if ($stockQuantityColumn === null) {
        $message = "Table stock introuvable ou colonne de quantité non reconnue.";
    } else {
        $stmtStock = $mysqli->prepare("SELECT `{$stockQuantityColumn}` AS stock_qty FROM stock WHERE article_id = ? LIMIT 1");

        if ($stmtStock === false) {
            $message = "Erreur SQL (prepare stock) : " . $mysqli->error;
        } else {
            $stmtStock->bind_param("i", $id);
            $stmtStock->execute();
            $resultStock = $stmtStock->get_result();
            $stockRow = $resultStock ? $resultStock->fetch_assoc() : null;
            $stmtStock->close();

            $availableStock = $stockRow ? intval($stockRow['stock_qty']) : 0;

            $stmtInCart = $mysqli->prepare("SELECT COUNT(*) AS qty FROM cart WHERE user_id = ? AND article_id = ?");
            if ($stmtInCart) {
                $stmtInCart->bind_param("ii", $userId, $id);
                $stmtInCart->execute();
                $resInCart = $stmtInCart->get_result();
                $inCartRow = $resInCart ? $resInCart->fetch_assoc() : ['qty' => 0];
                $alreadyInCart = intval($inCartRow['qty'] ?? 0);
                $stmtInCart->close();
            } else {
                $alreadyInCart = 0;
            }

            if ($availableStock <= 0) {
                $message = "Article en rupture de stock.";
            } elseif (($alreadyInCart + $quantity) > $availableStock) {
                $message = "Stock insuffisant. Disponible : " . max(0, $availableStock - $alreadyInCart);
            } else {
                $stmtCart = $mysqli->prepare("INSERT INTO cart (user_id, article_id) VALUES (?, ?)");

                if ($stmtCart === false) {
                    $message = "Erreur SQL (prepare cart) : " . $mysqli->error;
                } else {
                    $ok = true;
                    for ($i = 0; $i < $quantity; $i++) {
                        $stmtCart->bind_param("ii", $userId, $id);
                        if (!$stmtCart->execute()) {
                            $ok = false;
                            break;
                        }
                    }

                    if ($ok) {
                        $message = "Article ajouté au panier.";
                    } else {
                        $message = "Erreur lors de l'ajout au panier : " . $stmtCart->error;
                    }

                    $stmtCart->close();
                }
            }
        }
    }
}

$stmt = $mysqli->prepare("SELECT a.*, u.role AS auteur_role
                         FROM article a
                         LEFT JOIN user u ON u.id = a.auteur_id
                         WHERE a.id = ?
                         LIMIT 1");
if ($stmt === false) {
    die("Erreur SQL (prepare detail) : " . $mysqli->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
?>
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

    <form method="POST" action="Detail.php?id=<?php echo $id; ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="add_to_cart" value="1">

        <label>Quantité :</label><br>
        <input type="number" name="quantity" min="1" value="1" required><br><br>

        <button type="submit">Ajouter au panier</button>
    </form>

    <?php if ($canEdit) : ?>
        <form method="POST" action="Modifier.php">
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