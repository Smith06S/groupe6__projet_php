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

