<?php
require_once dirname(__DIR__, 2) . "/auth.php";
require_once dirname(__DIR__, 2) . "/db.php";


require_login();

$currentUserId = intval($_SESSION['user_id']);
$message = "";

$stmtRole = $mysqli->prepare("SELECT role FROM user WHERE id = ? LIMIT 1");
if ($stmtRole === false) {
    die("Erreur SQL (prepare role) : " . $mysqli->error);
}
$stmtRole->bind_param("i", $currentUserId);
$stmtRole->execute();
$resultRole = $stmtRole->get_result();
$currentUser = $resultRole ? $resultRole->fetch_assoc() : null;
$stmtRole->close();

if (!$currentUser || strtolower((string)($currentUser['role'] ?? '')) !== 'admin') {
    die("Accès refusé : zone administrateur uniquement.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_article') {
        $articleId = intval($_POST['article_id'] ?? 0);
        if ($articleId > 0) {
            $mysqli->begin_transaction();
            try {
                $stmt1 = $mysqli->prepare("DELETE FROM cart WHERE article_id = ?");
                if ($stmt1) {
                    $stmt1->bind_param("i", $articleId);
                    $stmt1->execute();
                    $stmt1->close();
                }

                $stmt2 = $mysqli->prepare("DELETE FROM stock WHERE article_id = ?");
                if ($stmt2) {
                    $stmt2->bind_param("i", $articleId);
                    $stmt2->execute();
                    $stmt2->close();
                }

                $stmt3 = $mysqli->prepare("DELETE FROM article WHERE id = ? LIMIT 1");
                if ($stmt3 === false) {
                    throw new Exception("Erreur SQL (delete article) : " . $mysqli->error);
                }
                $stmt3->bind_param("i", $articleId);
                if (!$stmt3->execute()) {
                    throw new Exception("Erreur suppression article : " . $stmt3->error);
                }
                $stmt3->close();

                $mysqli->commit();
                $message = "Article supprimé.";
            } catch (Exception $e) {
                $mysqli->rollback();
                $message = $e->getMessage();
            }
        }
    }

    if ($action === 'update_article') {
        $articleId = intval($_POST['article_id'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $prix = floatval($_POST['prix'] ?? 0);
        $image_url = trim($_POST['image_url'] ?? '');

        if ($articleId > 0 && $nom !== '' && $description !== '') {
            $stmt = $mysqli->prepare("UPDATE article SET nom = ?, description = ?, prix = ?, image_url = ? WHERE id = ?");
            if ($stmt === false) {
                $message = "Erreur SQL (update article) : " . $mysqli->error;
            } else {
                $stmt->bind_param("ssdsi", $nom, $description, $prix, $image_url, $articleId);
                if ($stmt->execute()) {
                    $message = "Article mis à jour.";
                } else {
                    $message = "Erreur mise à jour article : " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }

    if ($action === 'delete_user') {
        $userId = intval($_POST['user_id'] ?? 0);

        if ($userId === $currentUserId) {
            $message = "Impossible de supprimer votre propre compte admin.";
        } elseif ($userId > 0) {
            $stmt = $mysqli->prepare("DELETE FROM user WHERE id = ? LIMIT 1");
            if ($stmt === false) {
                $message = "Erreur SQL (delete user) : " . $mysqli->error;
            } else {
                if ($stmt->execute()) {
                    $message = "Utilisateur supprimé.";
                } else {
                    $message = "Erreur suppression utilisateur : " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }

    if ($action === 'update_user') {
        $userId = intval($_POST['user_id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $mail = trim($_POST['mail'] ?? '');
        $role = trim($_POST['role'] ?? 'user');
        $solde = floatval($_POST['solde'] ?? 0);

        if ($userId > 0 && $username !== '' && $mail !== '') {
            $stmtUnique = $mysqli->prepare("SELECT id FROM user WHERE (username = ? OR mail = ?) AND id <> ? LIMIT 1");
            if ($stmtUnique) {
                $stmtUnique->bind_param("ssi", $username, $mail, $userId);
                $stmtUnique->execute();
                $stmtUnique->store_result();
                if ($stmtUnique->num_rows > 0) {
                    $message = "Username ou email déjà utilisé.";
                }
                $stmtUnique->close();
            }

            if ($message === "" || $message === "Article mis à jour." || $message === "Utilisateur supprimé." || $message === "Article supprimé.") {
                $stmt = $mysqli->prepare("UPDATE user SET username = ?, mail = ?, role = ?, solde = ? WHERE id = ?");
                if ($stmt === false) {
                    $message = "Erreur SQL (update user) : " . $mysqli->error;
                } else {
                    $stmt->bind_param("sssdi", $username, $mail, $role, $solde, $userId);
                    if ($stmt->execute()) {
                        $message = "Utilisateur mis à jour.";
                    } else {
                        $message = "Erreur mise à jour utilisateur : " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}

$users = [];
$resUsers = $mysqli->query("SELECT id, username, mail, role, solde FROM user ORDER BY id DESC");
if ($resUsers) {
    $users = $resUsers->fetch_all(MYSQLI_ASSOC);
}

$articles = [];
$resArticles = $mysqli->query("SELECT id, nom, description, prix, image_url, auteur_id, date_publication FROM article ORDER BY id DESC");
if ($resArticles) {
    $articles = $resArticles->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<body>
<h1>Admin</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<h2>Utilisateurs</h2>
<?php foreach ($users as $user) : ?>
    <div>
        <form method="POST" action="Admin.php">
            <input type="hidden" name="action" value="update_user">
            <input type="hidden" name="user_id" value="<?php echo intval($user['id']); ?>">

            <p>ID : <?php echo intval($user['id']); ?></p>
            <label>Username :</label><br>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br>

            <label>Mail :</label><br>
            <input type="email" name="mail" value="<?php echo htmlspecialchars($user['mail']); ?>" required><br>

            <label>Role :</label><br>
            <input type="text" name="role" value="<?php echo htmlspecialchars($user['role']); ?>" required><br>

            <label>Solde :</label><br>
            <input type="number" step="0.01" name="solde" value="<?php echo htmlspecialchars($user['solde']); ?>" required><br><br>

            <button type="submit">Modifier utilisateur</button>
        </form>

        <form method="POST" action="Admin.php" onsubmit="return confirm('Supprimer cet utilisateur ?');">
            <input type="hidden" name="action" value="delete_user">
            <input type="hidden" name="user_id" value="<?php echo intval($user['id']); ?>">
            <button type="submit">Supprimer utilisateur</button>
        </form>
        <hr>
    </div>
<?php endforeach; ?>

<h2>Articles</h2>
<?php foreach ($articles as $article) : ?>
    <div>
        <form method="POST" action="Admin.php">
            <input type="hidden" name="action" value="update_article">
            <input type="hidden" name="article_id" value="<?php echo intval($article['id']); ?>">

            <p>ID : <?php echo intval($article['id']); ?> | Auteur : <?php echo intval($article['auteur_id']); ?></p>
            <label>Nom :</label><br>
            <input type="text" name="nom" value="<?php echo htmlspecialchars($article['nom']); ?>" required><br>

            <label>Description :</label><br>
            <textarea name="description" rows="3" cols="50" required><?php echo htmlspecialchars($article['description']); ?></textarea><br>

            <label>Prix :</label><br>
            <input type="number" step="0.01" min="0" name="prix" value="<?php echo htmlspecialchars($article['prix']); ?>" required><br>

            <label>Image URL :</label><br>
            <input type="text" name="image_url" value="<?php echo htmlspecialchars($article['image_url']); ?>"><br><br>

            <button type="submit">Modifier article</button>
        </form>

        <form method="POST" action="Admin.php" onsubmit="return confirm('Supprimer cet article ?');">
            <input type="hidden" name="action" value="delete_article">
            <input type="hidden" name="article_id" value="<?php echo intval($article['id']); ?>">
            <button type="submit">Supprimer article</button>
        </form>
        <hr>
    </div>
<?php endforeach; ?>
</body>
</html>
