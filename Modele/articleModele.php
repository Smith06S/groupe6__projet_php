<?php
require_once __DIR__ . '/../db/Database.php';

function getAllArticlesOrderedByDate() {
	$mysqli = dbConnect();
	$articles = [];

	$result = $mysqli->query("SELECT * FROM article ORDER BY date_publication DESC");

	if ($result) {
		while ($article = $result->fetch_assoc()) {
			$articles[] = $article;
		}
		$result->free();
	}

	$mysqli->close();

	return $articles;
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

    function getStockArticleColumn(mysqli $mysqli): ?string {
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

function getArticleById($articleId) {
    $mysqli = dbConnect();
    $stmt = $mysqli->prepare("SELECT a.*, u.role AS auteur_role
                              FROM article a
                              LEFT JOIN user u ON u.id = a.auteur_id
                              WHERE a.id = ?
                              LIMIT 1");

    if ($stmt === false) {
        $mysqli->close();
        return null;
    }

    $stmt->bind_param("i", $articleId);
    $stmt->execute();
    $result = $stmt->get_result();
    $article = $result ? $result->fetch_assoc() : null;

    $stmt->close();
    $mysqli->close();

    return $article;
}

function getArticleStockById($articleId): int {
    $mysqli = dbConnect();
    $stockQuantityColumn = getStockQuantityColumn($mysqli);
    $stockArticleColumn = getStockArticleColumn($mysqli);

    if ($stockQuantityColumn === null || $stockArticleColumn === null) {
        $mysqli->close();
        return 0;
    }

    $stmt = $mysqli->prepare("SELECT `{$stockQuantityColumn}` AS stock_qty FROM stock WHERE `{$stockArticleColumn}` = ? LIMIT 1");
    if ($stmt === false) {
        $mysqli->close();
        return 0;
    }

    $stmt->bind_param("i", $articleId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $qty = $row ? intval($row['stock_qty']) : 0;

    $stmt->close();
    $mysqli->close();

    return max(0, $qty);
}

function getArticleAvailableStockById($articleId): int {
    $mysqli = dbConnect();

    $stockQuantityColumn = getStockQuantityColumn($mysqli);
    $stockArticleColumn = getStockArticleColumn($mysqli);
    $cartArticleColumn = getCartArticleColumn($mysqli);

    if ($stockQuantityColumn === null || $stockArticleColumn === null || $cartArticleColumn === null) {
        $mysqli->close();
        return 0;
    }

    $sql = "SELECT
                COALESCE((
                    SELECT s.`{$stockQuantityColumn}`
                    FROM stock s
                    WHERE s.`{$stockArticleColumn}` = ?
                    LIMIT 1
                ), 0)
                -
                COALESCE((
                    SELECT COUNT(*)
                    FROM cart c
                    WHERE c.`{$cartArticleColumn}` = ?
                ), 0)
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
    $availableQty = intval($row['available_qty'] ?? 0);

    $stmt->close();
    $mysqli->close();

    return max(0, $availableQty);
}

function getCartQuantityForArticle($userId, $articleId): int {
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

    return $qty;
}

function addArticleToCart($userId, $articleId, $quantity): bool {
    $mysqli = dbConnect();
        $userColumn = getCartUserColumn($mysqli);
        $articleColumn = getCartArticleColumn($mysqli);

        if ($userColumn === null || $articleColumn === null) {
            $mysqli->close();
            return false;
        }

        $stmt = $mysqli->prepare("INSERT INTO cart (`{$userColumn}`, `{$articleColumn}`) VALUES (?, ?)");

    if ($stmt === false) {
        $mysqli->close();
        return false;
    }

    $ok = true;
    for ($i = 0; $i < $quantity; $i++) {
        $stmt->bind_param("ii", $userId, $articleId);
        if (!$stmt->execute()) {
            $ok = false;
            break;
        }
    }

    $stmt->close();
    $mysqli->close();

    return $ok;
}

function findArticleByNameDifferentAuthor($nom, $authorId) {
    $mysqli = dbConnect();
    $stmt = $mysqli->prepare("SELECT id FROM article WHERE nom = ? AND auteur_id <> ? ORDER BY id ASC LIMIT 1");

    if ($stmt === false) {
        $mysqli->close();
        return null;
    }

    $stmt->bind_param("si", $nom, $authorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;

    $stmt->close();
    $mysqli->close();

    return $row ? intval($row['id']) : null;
}

function createArticle($nom, $description, $prix, $imageUrl, $authorId): int {
    $mysqli = dbConnect();
    $stmt = $mysqli->prepare("INSERT INTO article (nom, description, prix, date_publication, image_url, auteur_id) VALUES (?, ?, ?, NOW(), ?, ?)");

    if ($stmt === false) {
        $mysqli->close();
        return 0;
    }

    $stmt->bind_param("ssdsi", $nom, $description, $prix, $imageUrl, $authorId);
    $ok = $stmt->execute();
    $id = $ok ? intval($stmt->insert_id) : 0;

    $stmt->close();
    $mysqli->close();

    return $id;
}

function insertStockForArticle($articleId, $quantity): bool {
    $mysqli = dbConnect();
    $stockQuantityColumn = getStockQuantityColumn($mysqli);
    $stockArticleColumn = getStockArticleColumn($mysqli);

    if ($stockQuantityColumn === null || $stockArticleColumn === null) {
        $mysqli->close();
        return false;
    }

    $stmt = $mysqli->prepare("INSERT INTO stock (`{$stockArticleColumn}`, `{$stockQuantityColumn}`) VALUES (?, ?)");
    if ($stmt === false) {
        $mysqli->close();
        return false;
    }

    $stmt->bind_param("ii", $articleId, $quantity);
    $ok = $stmt->execute();

    $stmt->close();
    $mysqli->close();

    return $ok;
}

function incrementStockForArticle($articleId, $quantity): bool {
    $mysqli = dbConnect();
    $stockQuantityColumn = getStockQuantityColumn($mysqli);
    $stockArticleColumn = getStockArticleColumn($mysqli);

    if ($stockQuantityColumn === null || $stockArticleColumn === null) {
        $mysqli->close();
        return false;
    }

    $stmt = $mysqli->prepare("UPDATE stock SET `{$stockQuantityColumn}` = `{$stockQuantityColumn}` + ? WHERE `{$stockArticleColumn}` = ?");
    if ($stmt === false) {
        $mysqli->close();
        return false;
    }

    $stmt->bind_param("ii", $quantity, $articleId);
    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;

    $stmt->close();

    if ($ok && $affected > 0) {
        $mysqli->close();
        return true;
    }

    $stmtInsert = $mysqli->prepare("INSERT INTO stock (`{$stockArticleColumn}`, `{$stockQuantityColumn}`) VALUES (?, ?)");
    if ($stmtInsert === false) {
        $mysqli->close();
        return false;
    }

    $stmtInsert->bind_param("ii", $articleId, $quantity);
    $insertOk = $stmtInsert->execute();
    $stmtInsert->close();
    $mysqli->close();

    return $insertOk;
}

function updateArticleById($articleId, $nom, $description, $prix, $imageUrl): bool {
    $mysqli = dbConnect();
    $stmt = $mysqli->prepare("UPDATE article SET nom = ?, description = ?, prix = ?, date_publication = NOW(), image_url = ? WHERE id = ?");

    if ($stmt === false) {
        $mysqli->close();
        return false;
    }

    $stmt->bind_param("ssdsi", $nom, $description, $prix, $imageUrl, $articleId);
    $ok = $stmt->execute();

    $stmt->close();
    $mysqli->close();

    return $ok;
}

function deleteArticleCascadeById($articleId): bool {
    $mysqli = dbConnect();

    $cartArticleColumn = getCartArticleColumn($mysqli);
    $stockArticleColumn = getStockArticleColumn($mysqli);

    $mysqli->begin_transaction();

    try {
        if ($cartArticleColumn !== null) {
            $stmtDeleteCart = $mysqli->prepare("DELETE FROM cart WHERE `{$cartArticleColumn}` = ?");
            if ($stmtDeleteCart === false) {
                throw new Exception('Erreur SQL (delete cart).');
            }
            $stmtDeleteCart->bind_param("i", $articleId);
            $stmtDeleteCart->execute();
            $stmtDeleteCart->close();
        }

        if ($stockArticleColumn !== null) {
            $stmtDeleteStock = $mysqli->prepare("DELETE FROM stock WHERE `{$stockArticleColumn}` = ?");
            if ($stmtDeleteStock === false) {
                throw new Exception('Erreur SQL (delete stock).');
            }
            $stmtDeleteStock->bind_param("i", $articleId);
            $stmtDeleteStock->execute();
            $stmtDeleteStock->close();
        }

        $stmtDeleteArticle = $mysqli->prepare("DELETE FROM article WHERE id = ? LIMIT 1");
        if ($stmtDeleteArticle === false) {
            throw new Exception('Erreur SQL (delete article).');
        }
        $stmtDeleteArticle->bind_param("i", $articleId);
        if (!$stmtDeleteArticle->execute()) {
            throw new Exception('Erreur suppression article.');
        }
        $stmtDeleteArticle->close();

        $mysqli->commit();
        $mysqli->close();
        return true;
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return false;
    }
}