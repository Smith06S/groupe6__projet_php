<?php
require_once __DIR__ . '/../db/Database.php';

function invoice_getCartUserColumn(mysqli $mysqli): ?string {
	$result = $mysqli->query("SHOW COLUMNS FROM cart");
	if (!$result) {
		return null;
	}

	$columns = [];
	while ($row = $result->fetch_assoc()) {
		$columns[] = strtolower($row['Field']);
	}

	$candidates = ['user_id', 'userid', 'id_user', 'user'];
	foreach ($candidates as $candidate) {
		if (in_array($candidate, $columns, true)) {
			return $candidate;
		}
	}

	return null;
}

function invoice_getCartArticleColumn(mysqli $mysqli): ?string {
	$result = $mysqli->query("SHOW COLUMNS FROM cart");
	if (!$result) {
		return null;
	}

	$columns = [];
	while ($row = $result->fetch_assoc()) {
		$columns[] = strtolower($row['Field']);
	}

	$candidates = ['article_id', 'articleid', 'id_article', 'product_id', 'id_product', 'article'];
	foreach ($candidates as $candidate) {
		if (in_array($candidate, $columns, true)) {
			return $candidate;
		}
	}

	return null;
}

function invoice_getStockQuantityColumn(mysqli $mysqli): ?string {
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

function invoice_getStockArticleColumn(mysqli $mysqli): ?string {
	$result = $mysqli->query("SHOW COLUMNS FROM stock");
	if (!$result) {
		return null;
	}

	$columns = [];
	while ($row = $result->fetch_assoc()) {
		$columns[] = strtolower($row['Field']);
	}

	$candidates = ['article_id', 'id_article', 'product_id', 'id_product', 'article'];
	foreach ($candidates as $candidate) {
		if (in_array($candidate, $columns, true)) {
			return $candidate;
		}
	}

	return null;
}

function getCheckoutData($userId): array {
	$mysqli = dbConnect();
	$userColumn = invoice_getCartUserColumn($mysqli);
	$articleColumn = invoice_getCartArticleColumn($mysqli);

	$user = null;
	$cartItems = [];
	$total = 0.0;

	$stmtUser = $mysqli->prepare("SELECT id, solde FROM user WHERE id = ? LIMIT 1");
	if ($stmtUser) {
		$stmtUser->bind_param("i", $userId);
		$stmtUser->execute();
		$resUser = $stmtUser->get_result();
		$user = $resUser ? $resUser->fetch_assoc() : null;
		$stmtUser->close();
	}

	if ($userColumn !== null && $articleColumn !== null) {
		$sql = "SELECT a.id AS article_id,
					   a.nom,
					   a.prix,
					   COUNT(*) AS quantity,
					   SUM(a.prix) AS subtotal
				FROM cart c
				INNER JOIN article a ON a.id = c.`{$articleColumn}`
				WHERE c.`{$userColumn}` = ?
				GROUP BY a.id, a.nom, a.prix
				ORDER BY a.date_publication DESC";
		$stmtItems = $mysqli->prepare($sql);
		if ($stmtItems) {
			$stmtItems->bind_param("i", $userId);
			$stmtItems->execute();
			$resItems = $stmtItems->get_result();
			$cartItems = $resItems ? $resItems->fetch_all(MYSQLI_ASSOC) : [];
			$stmtItems->close();
		}
	}

	foreach ($cartItems as $item) {
		$total += floatval($item['subtotal'] ?? 0);
	}

	$mysqli->close();

	return [
		'user' => $user,
		'cartItems' => $cartItems,
		'total' => $total,
	];
}

function validateAndCreateInvoice($userId, $billingAddress, $billingCity, $billingZip): array {
	$mysqli = dbConnect();

	$userColumn = invoice_getCartUserColumn($mysqli);
	$articleColumn = invoice_getCartArticleColumn($mysqli);
	$stockQtyColumn = invoice_getStockQuantityColumn($mysqli);
	$stockArticleColumn = invoice_getStockArticleColumn($mysqli);

	if ($userColumn === null || $articleColumn === null) {
		$mysqli->close();
		return ['success' => false, 'message' => 'Structure de table cart invalide.'];
	}

	if ($stockQtyColumn === null || $stockArticleColumn === null) {
		$mysqli->close();
		return ['success' => false, 'message' => 'Structure de table stock invalide.'];
	}

	$checkInvoiceItem = $mysqli->query("SHOW TABLES LIKE 'invoice_item'");
	if (!$checkInvoiceItem || $checkInvoiceItem->num_rows === 0) {
		$mysqli->close();
		return ['success' => false, 'message' => 'Table invoice_item introuvable.'];
	}

	$checkoutData = getCheckoutData($userId);
	$user = $checkoutData['user'];
	$cartItems = $checkoutData['cartItems'];
	$total = floatval($checkoutData['total']);

	if (!$user) {
		$mysqli->close();
		return ['success' => false, 'message' => 'Utilisateur introuvable.'];
	}

	if (empty($cartItems)) {
		$mysqli->close();
		return ['success' => false, 'message' => 'Ton panier est vide.'];
	}

	if (floatval($user['solde'] ?? 0) < $total) {
		$mysqli->close();
		return ['success' => false, 'message' => 'Solde insuffisant pour valider la commande.'];
	}

	$mysqli->begin_transaction();

	try {
		$stmtStockCheck = $mysqli->prepare("SELECT `{$stockQtyColumn}` AS stock_qty FROM stock WHERE `{$stockArticleColumn}` = ? LIMIT 1");
		if ($stmtStockCheck === false) {
			throw new Exception('Erreur SQL (stock check).');
		}

		foreach ($cartItems as $item) {
			$articleId = intval($item['article_id']);
			$requestedQty = intval($item['quantity']);

			$stmtStockCheck->bind_param("i", $articleId);
			$stmtStockCheck->execute();
			$resStock = $stmtStockCheck->get_result();
			$rowStock = $resStock ? $resStock->fetch_assoc() : null;
			$currentStock = intval($rowStock['stock_qty'] ?? 0);

			if ($currentStock < $requestedQty) {
				throw new Exception("Stock insuffisant pour l'article ID {$articleId}.");
			}
		}
		$stmtStockCheck->close();

		$stmtInvoice = $mysqli->prepare("INSERT INTO invoice (user_id, transaction_date, montant, facturation_address, facturation_city, facturation_zip)
										 VALUES (?, NOW(), ?, ?, ?, ?)");
		if ($stmtInvoice === false) {
			throw new Exception('Erreur SQL (invoice).');
		}
		$stmtInvoice->bind_param("idsss", $userId, $total, $billingAddress, $billingCity, $billingZip);
		if (!$stmtInvoice->execute()) {
			throw new Exception('Erreur création facture.');
		}
		$invoiceId = intval($stmtInvoice->insert_id);
		$stmtInvoice->close();

		$stmtInvoiceItem = $mysqli->prepare("INSERT INTO invoice_item (invoice_id, article_id, quantity, price) VALUES (?, ?, ?, ?)");
		if ($stmtInvoiceItem === false) {
			throw new Exception('Erreur SQL (invoice item).');
		}
		foreach ($cartItems as $item) {
			$articleId = intval($item['article_id']);
			$qty = intval($item['quantity']);
			$price = floatval($item['prix']);
			$stmtInvoiceItem->bind_param("iiid", $invoiceId, $articleId, $qty, $price);
			if (!$stmtInvoiceItem->execute()) {
				throw new Exception('Erreur création ligne facture.');
			}
		}
		$stmtInvoiceItem->close();

		$stmtBalance = $mysqli->prepare("UPDATE user SET solde = solde - ? WHERE id = ? AND solde >= ?");
		if ($stmtBalance === false) {
			throw new Exception('Erreur SQL (débit solde).');
		}
		$stmtBalance->bind_param("did", $total, $userId, $total);
		if (!$stmtBalance->execute() || $stmtBalance->affected_rows !== 1) {
			throw new Exception('Impossible de débiter le solde.');
		}
		$stmtBalance->close();

		$stmtStockUpdate = $mysqli->prepare("UPDATE stock SET `{$stockQtyColumn}` = `{$stockQtyColumn}` - ? WHERE `{$stockArticleColumn}` = ? AND `{$stockQtyColumn}` >= ?");
		if ($stmtStockUpdate === false) {
			throw new Exception('Erreur SQL (update stock).');
		}
		foreach ($cartItems as $item) {
			$qty = intval($item['quantity']);
			$articleId = intval($item['article_id']);
			$stmtStockUpdate->bind_param("iii", $qty, $articleId, $qty);
			if (!$stmtStockUpdate->execute() || $stmtStockUpdate->affected_rows !== 1) {
				throw new Exception("Impossible de mettre à jour le stock pour l'article ID {$articleId}.");
			}
		}
		$stmtStockUpdate->close();

		$stmtClear = $mysqli->prepare("DELETE FROM cart WHERE `{$userColumn}` = ?");
		if ($stmtClear === false) {
			throw new Exception('Erreur SQL (clear cart).');
		}
		$stmtClear->bind_param("i", $userId);
		if (!$stmtClear->execute()) {
			throw new Exception('Erreur suppression panier.');
		}
		$stmtClear->close();

		$mysqli->commit();
		$mysqli->close();

		return ['success' => true, 'message' => 'Commande validée, facture générée, panier vidé.'];
	} catch (Exception $e) {
		$mysqli->rollback();
		$mysqli->close();
		return ['success' => false, 'message' => $e->getMessage()];
	}
}