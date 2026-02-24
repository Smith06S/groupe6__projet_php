<?php
require_once __DIR__ . '/../db/Database.php';

function admin_getCartUserColumn(mysqli $mysqli): ?string {
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

function admin_getCartArticleColumn(mysqli $mysqli): ?string {
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

function admin_getStockArticleColumn(mysqli $mysqli): ?string {
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

function adminGetAllArticles(): array {
    $mysqli = dbConnect();
    $sql = "SELECT a.id, a.nom, a.description, a.prix, a.date_publication, a.image_url, a.auteur_id, u.username AS auteur_username
            FROM article a
            LEFT JOIN user u ON u.id = a.auteur_id
            ORDER BY a.date_publication DESC, a.id DESC";

    $result = $mysqli->query($sql);
    $articles = [];
    if ($result) {
        $articles = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    }

    $mysqli->close();
    return $articles;
}

function adminGetAllUsers(): array {
    $mysqli = dbConnect();
    $result = $mysqli->query("SELECT id, username, mail, solde, photo_profil, role FROM user ORDER BY id DESC");

    $users = [];
    if ($result) {
        $users = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    }

    $mysqli->close();
    return $users;
}

function adminUpdateUserById($userId, $username, $mail, $role, $solde, $photoProfil): bool {
    $mysqli = dbConnect();
    $stmt = $mysqli->prepare("UPDATE user SET username = ?, mail = ?, role = ?, solde = ?, photo_profil = ? WHERE id = ?");

    if ($stmt === false) {
        $mysqli->close();
        return false;
    }

    $stmt->bind_param("sssdsi", $username, $mail, $role, $solde, $photoProfil, $userId);
    $ok = $stmt->execute();

    $stmt->close();
    $mysqli->close();
    return $ok;
}

function adminDeleteArticleById($articleId): bool {
    $mysqli = dbConnect();
    $cartArticleColumn = admin_getCartArticleColumn($mysqli);
    $stockArticleColumn = admin_getStockArticleColumn($mysqli);

    $mysqli->begin_transaction();

    try {
        if ($cartArticleColumn !== null) {
            $stmtCart = $mysqli->prepare("DELETE FROM cart WHERE `{$cartArticleColumn}` = ?");
            if ($stmtCart === false) {
                throw new Exception('Erreur suppression cart article.');
            }
            $stmtCart->bind_param("i", $articleId);
            $stmtCart->execute();
            $stmtCart->close();
        }

        if ($stockArticleColumn !== null) {
            $stmtStock = $mysqli->prepare("DELETE FROM stock WHERE `{$stockArticleColumn}` = ?");
            if ($stmtStock === false) {
                throw new Exception('Erreur suppression stock article.');
            }
            $stmtStock->bind_param("i", $articleId);
            $stmtStock->execute();
            $stmtStock->close();
        }

        $stmtInvoiceItem = $mysqli->prepare("DELETE FROM invoice_item WHERE article_id = ?");
        if ($stmtInvoiceItem !== false) {
            $stmtInvoiceItem->bind_param("i", $articleId);
            $stmtInvoiceItem->execute();
            $stmtInvoiceItem->close();
        }

        $stmtArticle = $mysqli->prepare("DELETE FROM article WHERE id = ? LIMIT 1");
        if ($stmtArticle === false) {
            throw new Exception('Erreur suppression article.');
        }
        $stmtArticle->bind_param("i", $articleId);
        if (!$stmtArticle->execute()) {
            throw new Exception('Erreur suppression article.');
        }
        $stmtArticle->close();

        $mysqli->commit();
        $mysqli->close();
        return true;
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return false;
    }
}

function adminDeleteUserById($userId): bool {
    $mysqli = dbConnect();

    $cartUserColumn = admin_getCartUserColumn($mysqli);
    $cartArticleColumn = admin_getCartArticleColumn($mysqli);
    $stockArticleColumn = admin_getStockArticleColumn($mysqli);

    $mysqli->begin_transaction();

    try {
        $stmtArticles = $mysqli->prepare("SELECT id FROM article WHERE auteur_id = ?");
        if ($stmtArticles !== false) {
            $stmtArticles->bind_param("i", $userId);
            $stmtArticles->execute();
            $resArticles = $stmtArticles->get_result();
            $articleIds = $resArticles ? $resArticles->fetch_all(MYSQLI_ASSOC) : [];
            $stmtArticles->close();

            foreach ($articleIds as $articleRow) {
                $articleId = intval($articleRow['id']);

                if ($cartArticleColumn !== null) {
                    $stmtCartArticle = $mysqli->prepare("DELETE FROM cart WHERE `{$cartArticleColumn}` = ?");
                    if ($stmtCartArticle === false) {
                        throw new Exception('Erreur suppression cart article user.');
                    }
                    $stmtCartArticle->bind_param("i", $articleId);
                    $stmtCartArticle->execute();
                    $stmtCartArticle->close();
                }

                if ($stockArticleColumn !== null) {
                    $stmtStock = $mysqli->prepare("DELETE FROM stock WHERE `{$stockArticleColumn}` = ?");
                    if ($stmtStock === false) {
                        throw new Exception('Erreur suppression stock article user.');
                    }
                    $stmtStock->bind_param("i", $articleId);
                    $stmtStock->execute();
                    $stmtStock->close();
                }

                $stmtInvoiceItemByArticle = $mysqli->prepare("DELETE FROM invoice_item WHERE article_id = ?");
                if ($stmtInvoiceItemByArticle !== false) {
                    $stmtInvoiceItemByArticle->bind_param("i", $articleId);
                    $stmtInvoiceItemByArticle->execute();
                    $stmtInvoiceItemByArticle->close();
                }
            }
        }

        $stmtDeleteArticles = $mysqli->prepare("DELETE FROM article WHERE auteur_id = ?");
        if ($stmtDeleteArticles === false) {
            throw new Exception('Erreur suppression articles user.');
        }
        $stmtDeleteArticles->bind_param("i", $userId);
        $stmtDeleteArticles->execute();
        $stmtDeleteArticles->close();

        if ($cartUserColumn !== null) {
            $stmtDeleteCartUser = $mysqli->prepare("DELETE FROM cart WHERE `{$cartUserColumn}` = ?");
            if ($stmtDeleteCartUser === false) {
                throw new Exception('Erreur suppression cart user.');
            }
            $stmtDeleteCartUser->bind_param("i", $userId);
            $stmtDeleteCartUser->execute();
            $stmtDeleteCartUser->close();
        }

        $stmtInvoiceItems = $mysqli->prepare("DELETE ii FROM invoice_item ii INNER JOIN invoice i ON i.id = ii.invoice_id WHERE i.user_id = ?");
        if ($stmtInvoiceItems !== false) {
            $stmtInvoiceItems->bind_param("i", $userId);
            $stmtInvoiceItems->execute();
            $stmtInvoiceItems->close();
        }

        $stmtInvoices = $mysqli->prepare("DELETE FROM invoice WHERE user_id = ?");
        if ($stmtInvoices !== false) {
            $stmtInvoices->bind_param("i", $userId);
            $stmtInvoices->execute();
            $stmtInvoices->close();
        }

        $stmtUser = $mysqli->prepare("DELETE FROM user WHERE id = ? LIMIT 1");
        if ($stmtUser === false) {
            throw new Exception('Erreur suppression user.');
        }
        $stmtUser->bind_param("i", $userId);
        if (!$stmtUser->execute()) {
            throw new Exception('Erreur suppression user.');
        }
        $stmtUser->close();

        $mysqli->commit();
        $mysqli->close();
        return true;
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return false;
    }
}
