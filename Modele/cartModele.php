<?php
require_once __DIR__ . '/../db/Database.php';

function getCartUserColumn(mysqli $mysqli): ?string {
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

function getCartArticleColumn(mysqli $mysqli): ?string {
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

function getCartIdColumn(mysqli $mysqli): ?string {
	$result = $mysqli->query("SHOW COLUMNS FROM cart");
	if (!$result) {
		return null;
	}

	$columns = [];
	while ($row = $result->fetch_assoc()) {
		$columns[] = strtolower($row['Field']);
	}

	$candidates = ['id', 'cart_id', 'id_cart'];
	foreach ($candidates as $candidate) {
		if (in_array($candidate, $columns, true)) {
			return $candidate;
		}
	}

	return null;
}

function getStockQuantityColumnForCart(mysqli $mysqli): ?string {
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

function getStockArticleColumnForCart(mysqli $mysqli): ?string {
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

function getAvailableStockForCartArticle($articleId): int {
	$mysqli = dbConnect();
	$stockQtyColumn = getStockQuantityColumnForCart($mysqli);
	$stockArticleColumn = getStockArticleColumnForCart($mysqli);
	$cartArticleColumn = getCartArticleColumn($mysqli);

	if ($stockQtyColumn === null || $stockArticleColumn === null || $cartArticleColumn === null) {
		$mysqli->close();
		return 0;
	}

	$sql = "SELECT
				COALESCE((SELECT s.`{$stockQtyColumn}` FROM stock s WHERE s.`{$stockArticleColumn}` = ? LIMIT 1), 0)
				-
				COALESCE((SELECT COUNT(*) FROM cart c WHERE c.`{$cartArticleColumn}` = ?), 0)
				AS available_qty";

	$stmt = $mysqli->prepare($sql);
	if ($stmt === false) {
		$mysqli->close();
		return 0;
	}

	$stmt->bind_param("ii", $articleId, $articleId);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result ? $result->fetch_assoc() : null;
	$qty = intval($row['available_qty'] ?? 0);

	$stmt->close();
	$mysqli->close();

	return max(0, $qty);
}

function getUserCartQuantityForArticle($userId, $articleId): int {
	$mysqli = dbConnect();
	$userColumn = getCartUserColumn($mysqli);
	$articleColumn = getCartArticleColumn($mysqli);

	if ($userColumn === null || $articleColumn === null) {
		$mysqli->close();
		return 0;
	}

	$stmt = $mysqli->prepare("SELECT COUNT(*) AS qty FROM cart WHERE `{$userColumn}` = ? AND `{$articleColumn}` = ?");
	if ($stmt === false) {
		$mysqli->close();
		return 0;
	}

	$stmt->bind_param("ii", $userId, $articleId);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result ? $result->fetch_assoc() : null;
	$qty = intval($row['qty'] ?? 0);

	$stmt->close();
	$mysqli->close();

	return max(0, $qty);
}

function incrementCartArticleQuantity($userId, $articleId): array {
	$available = getAvailableStockForCartArticle($articleId);
	if ($available <= 0) {
		return ['ok' => false, 'reason' => 'max'];
	}

	$mysqli = dbConnect();
	$userColumn = getCartUserColumn($mysqli);
	$articleColumn = getCartArticleColumn($mysqli);

	if ($userColumn === null || $articleColumn === null) {
		$mysqli->close();
		return ['ok' => false, 'reason' => 'schema'];
	}

	$stmt = $mysqli->prepare("INSERT INTO cart (`{$userColumn}`, `{$articleColumn}`) VALUES (?, ?)");
	if ($stmt === false) {
		$mysqli->close();
		return ['ok' => false, 'reason' => 'sql'];
	}

	$stmt->bind_param("ii", $userId, $articleId);
	$ok = $stmt->execute();

	$stmt->close();
	$mysqli->close();

	return ['ok' => $ok, 'reason' => $ok ? '' : 'sql'];
}

function decrementCartArticleQuantity($userId, $articleId): array {
	$currentQty = getUserCartQuantityForArticle($userId, $articleId);
	if ($currentQty <= 1) {
		return ['ok' => false, 'reason' => 'min'];
	}

	$mysqli = dbConnect();
	$idColumn = getCartIdColumn($mysqli);
	$userColumn = getCartUserColumn($mysqli);
	$articleColumn = getCartArticleColumn($mysqli);

	if ($idColumn === null || $userColumn === null || $articleColumn === null) {
		$mysqli->close();
		return ['ok' => false, 'reason' => 'schema'];
	}

	$sql = "DELETE FROM cart WHERE `{$idColumn}` = (
				SELECT id_to_delete FROM (
					SELECT `{$idColumn}` AS id_to_delete
					FROM cart
					WHERE `{$userColumn}` = ? AND `{$articleColumn}` = ?
					ORDER BY `{$idColumn}` ASC
					LIMIT 1
				) x
			)";

	$stmt = $mysqli->prepare($sql);
	if ($stmt === false) {
		$mysqli->close();
		return ['ok' => false, 'reason' => 'sql'];
	}

	$stmt->bind_param("ii", $userId, $articleId);
	$ok = $stmt->execute();

	$stmt->close();
	$mysqli->close();

	return ['ok' => $ok, 'reason' => $ok ? '' : 'sql'];
}

function getMaxAllowedCartQuantityForArticle($userId, $articleId): int {
	$currentQty = getUserCartQuantityForArticle($userId, $articleId);
	$availableToAdd = getAvailableStockForCartArticle($articleId);
	return max(1, $currentQty + $availableToAdd);
}

function setCartArticleQuantity($userId, $articleId, $targetQty): array {
	$currentQty = getUserCartQuantityForArticle($userId, $articleId);
	if ($currentQty <= 0) {
		return ['ok' => false, 'reason' => 'not_found', 'applied' => 0];
	}

	$maxAllowed = getMaxAllowedCartQuantityForArticle($userId, $articleId);
	$sanitizedTarget = intval($targetQty);
	if ($sanitizedTarget < 1) {
		$sanitizedTarget = 1;
	}
	if ($sanitizedTarget > $maxAllowed) {
		$sanitizedTarget = $maxAllowed;
	}

	if ($sanitizedTarget === $currentQty) {
		return ['ok' => true, 'reason' => 'unchanged', 'applied' => $sanitizedTarget];
	}

	if ($sanitizedTarget > $currentQty) {
		$toAdd = $sanitizedTarget - $currentQty;
		for ($i = 0; $i < $toAdd; $i++) {
			$result = incrementCartArticleQuantity($userId, $articleId);
			if (empty($result['ok'])) {
				return ['ok' => false, 'reason' => $result['reason'] ?? 'sql', 'applied' => getUserCartQuantityForArticle($userId, $articleId)];
			}
		}
	} else {
		$toRemove = $currentQty - $sanitizedTarget;
		for ($i = 0; $i < $toRemove; $i++) {
			$result = decrementCartArticleQuantity($userId, $articleId);
			if (empty($result['ok'])) {
				return ['ok' => false, 'reason' => $result['reason'] ?? 'sql', 'applied' => getUserCartQuantityForArticle($userId, $articleId)];
			}
		}
	}

	return ['ok' => true, 'reason' => 'updated', 'applied' => $sanitizedTarget];
}

function getUserCartItems($userId): array {
	$mysqli = dbConnect();
	$userColumn = getCartUserColumn($mysqli);
	$articleColumn = getCartArticleColumn($mysqli);

	if ($userColumn === null || $articleColumn === null) {
		$mysqli->close();
		return [];
	}

	$sql = "SELECT a.id AS article_id,
				   a.nom,
				   a.description,
				   a.prix,
				   a.image_url,
				   COUNT(*) AS quantity,
				   SUM(a.prix) AS subtotal
			FROM cart c
			INNER JOIN article a ON a.id = c.`{$articleColumn}`
			WHERE c.`{$userColumn}` = ?
			GROUP BY a.id, a.nom, a.description, a.prix, a.image_url
			ORDER BY a.date_publication DESC";

	$stmt = $mysqli->prepare($sql);
	if ($stmt === false) {
		$mysqli->close();
		return [];
	}

	$stmt->bind_param("i", $userId);
	$stmt->execute();
	$result = $stmt->get_result();
	$items = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

	$stmt->close();
	$mysqli->close();

	return $items;
}

function removeArticleFromCart($userId, $articleId): bool {
	$mysqli = dbConnect();
	$userColumn = getCartUserColumn($mysqli);
	$articleColumn = getCartArticleColumn($mysqli);

	if ($userColumn === null || $articleColumn === null) {
		$mysqli->close();
		return false;
	}

	$stmt = $mysqli->prepare("DELETE FROM cart WHERE `{$userColumn}` = ? AND `{$articleColumn}` = ?");
	if ($stmt === false) {
		$mysqli->close();
		return false;
	}

	$stmt->bind_param("ii", $userId, $articleId);
	$ok = $stmt->execute();

	$stmt->close();
	$mysqli->close();

	return $ok;
}