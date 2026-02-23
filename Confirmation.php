<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

require_login();

$userId = intval($_SESSION['user_id']);
$message = "";
$success = false;

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

$invoiceItemReady = false;
$checkInvoiceItem = $mysqli->query("SHOW TABLES LIKE 'invoice_item'");
if ($checkInvoiceItem && $checkInvoiceItem->num_rows > 0) {
    $invoiceItemReady = true;
}

$stmtUser = $mysqli->prepare("SELECT solde FROM user WHERE id = ? LIMIT 1");
if ($stmtUser === false) {
    die("Erreur SQL (prepare user) : " . $mysqli->error);
}
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser ? $resultUser->fetch_assoc() : null;
$stmtUser->close();

if (!$user) {
    die("Utilisateur introuvable.");
}

$cartItems = [];
$total = 0;

$stmtItems = $mysqli->prepare("SELECT a.id AS article_id,
                                     a.nom,
                                     a.prix,
                                     COUNT(*) AS quantity,
                                     SUM(a.prix) AS subtotal
                              FROM cart c
                              INNER JOIN article a ON a.id = c.article_id
                              WHERE c.user_id = ?
                              GROUP BY a.id, a.nom, a.prix
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['validate_order'])) {
    $billingAddress = trim($_POST['facturation_address'] ?? '');
    $billingCity = trim($_POST['facturation_city'] ?? '');
    $billingPostalCode = trim($_POST['facturation_zip'] ?? '');

    if (empty($cartItems)) {
        $message = "Ton panier est vide.";
    } elseif ($billingAddress === '' || $billingCity === '' || $billingPostalCode === '') {
        $message = "Merci de remplir toutes les informations de facturation.";
    } elseif (floatval($user['solde']) < $total) {
        $message = "Solde insuffisant pour valider la commande.";
    } elseif ($stockQuantityColumn === null) {
        $message = "Table stock introuvable ou colonne de quantité non reconnue.";
    } elseif (!$invoiceItemReady) {
        $message = "Table invoice_item introuvable ou impossible a creer.";
    } else {
        $mysqli->begin_transaction();

        try {
            $stmtStockCheck = $mysqli->prepare("SELECT `{$stockQuantityColumn}` AS stock_qty FROM stock WHERE article_id = ? LIMIT 1");
            if ($stmtStockCheck === false) {
                throw new Exception("Erreur SQL (prepare stock check) : " . $mysqli->error);
            }

            foreach ($cartItems as $item) {
                $articleId = intval($item['article_id']);
                $requestedQty = intval($item['quantity']);

                $stmtStockCheck->bind_param("i", $articleId);
                $stmtStockCheck->execute();
                $stockResult = $stmtStockCheck->get_result();
                $stockRow = $stockResult ? $stockResult->fetch_assoc() : null;
                $currentStock = intval($stockRow['stock_qty'] ?? 0);

                if ($currentStock < $requestedQty) {
                    throw new Exception("Stock insuffisant pour l'article ID " . $articleId . ".");
                }
            }
            $stmtStockCheck->close();

            $stmtInvoice = $mysqli->prepare("INSERT INTO invoice (user_id, transaction_date, montant, facturation_address, facturation_city, facturation_zip)
                                            VALUES (?, NOW(), ?, ?, ?, ?)");
            if ($stmtInvoice === false) {
                throw new Exception("Erreur SQL (prepare invoice) : " . $mysqli->error);
            }
            $stmtInvoice->bind_param("idsss", $userId, $total, $billingAddress, $billingCity, $billingPostalCode);
            if (!$stmtInvoice->execute()) {
                throw new Exception("Erreur création facture : " . $stmtInvoice->error);
            }
            $invoiceId = intval($stmtInvoice->insert_id);
            $stmtInvoice->close();

            $stmtInvoiceItem = $mysqli->prepare("INSERT INTO invoice_item (invoice_id, article_id, quantity, price) VALUES (?, ?, ?, ?)");
            if ($stmtInvoiceItem === false) {
                throw new Exception("Erreur SQL (prepare invoice item) : " . $mysqli->error);
            }

            foreach ($cartItems as $item) {
                $articleId = intval($item['article_id']);
                $qty = intval($item['quantity']);
                $price = floatval($item['prix']);
                $stmtInvoiceItem->bind_param("iiid", $invoiceId, $articleId, $qty, $price);
                if (!$stmtInvoiceItem->execute()) {
                    throw new Exception("Erreur creation ligne facture : " . $stmtInvoiceItem->error);
                }
            }
            $stmtInvoiceItem->close();

            $stmtBalance = $mysqli->prepare("UPDATE user SET solde = solde - ? WHERE id = ? AND solde >= ?");
            if ($stmtBalance === false) {
                throw new Exception("Erreur SQL (prepare solde) : " . $mysqli->error);
            }
            $stmtBalance->bind_param("did", $total, $userId, $total);
            if (!$stmtBalance->execute() || $stmtBalance->affected_rows !== 1) {
                throw new Exception("Impossible de débiter le solde.");
            }
            $stmtBalance->close();

            $stmtStockUpdate = $mysqli->prepare("UPDATE stock SET `{$stockQuantityColumn}` = `{$stockQuantityColumn}` - ? WHERE article_id = ? AND `{$stockQuantityColumn}` >= ?");
            if ($stmtStockUpdate === false) {
                throw new Exception("Erreur SQL (prepare stock update) : " . $mysqli->error);
            }

            foreach ($cartItems as $item) {
                $qty = intval($item['quantity']);
                $articleId = intval($item['article_id']);
                $stmtStockUpdate->bind_param("iii", $qty, $articleId, $qty);
                if (!$stmtStockUpdate->execute() || $stmtStockUpdate->affected_rows !== 1) {
                    throw new Exception("Impossible de mettre à jour le stock pour l'article ID " . $articleId . ".");
                }
            }
            $stmtStockUpdate->close();

            $stmtClear = $mysqli->prepare("DELETE FROM cart WHERE user_id = ?");
            if ($stmtClear === false) {
                throw new Exception("Erreur SQL (prepare clear cart) : " . $mysqli->error);
            }
            $stmtClear->bind_param("i", $userId);
            if (!$stmtClear->execute()) {
                throw new Exception("Erreur suppression panier : " . $stmtClear->error);
            }
            $stmtClear->close();

            $mysqli->commit();
            $success = true;
            $message = "Commande validée, facture générée, panier vidé.";
            $cartItems = [];
            $total = 0;

            $stmtUserRefresh = $mysqli->prepare("SELECT solde FROM user WHERE id = ? LIMIT 1");
            if ($stmtUserRefresh) {
                $stmtUserRefresh->bind_param("i", $userId);
                $stmtUserRefresh->execute();
                $res = $stmtUserRefresh->get_result();
                $updatedUser = $res ? $res->fetch_assoc() : null;
                if ($updatedUser) {
                    $user = $updatedUser;
                }
                $stmtUserRefresh->close();
            }
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
<h1>Confirmation</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<p>Solde actuel : <?php echo htmlspecialchars(number_format(floatval($user['solde']), 2, '.', '')); ?></p>

<?php if (empty($cartItems)) : ?>
    <p>Ton panier est vide.</p>
    <a href="Panier.php">Retour au panier</a>
<?php else : ?>
    <h2>Récapitulatif</h2>
    <?php foreach ($cartItems as $item) : ?>
        <p>
            <?php echo htmlspecialchars($item['nom']); ?> |
            Quantité: <?php echo intval($item['quantity']); ?> |
            Sous-total: <?php echo htmlspecialchars(number_format(floatval($item['subtotal']), 2, '.', '')); ?>
        </p>
    <?php endforeach; ?>

    <h3>Total commande : <?php echo htmlspecialchars(number_format($total, 2, '.', '')); ?></h3>

    <h2>Informations de facturation</h2>
    <form method="POST" action="Confirmation.php">
        <input type="hidden" name="validate_order" value="1">

        <label>Adresse :</label><br>
        <input type="text" name="facturation_address" required><br><br>

        <label>Ville :</label><br>
        <input type="text" name="facturation_city" required><br><br>

        <label>Code postal :</label><br>
        <input type="text" name="facturation_zip" required><br><br>

        <button type="submit">Valider la commande</button>
    </form>

    <p><a href="Panier.php">Retour au panier</a></p>
<?php endif; ?>

<?php if ($success) : ?>
    <p><a href="Home.php">Retour à l'accueil</a></p>
<?php endif; ?>
</body>
</html>
