<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

require_login();

$message = "";
$article = null;

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    die("ID d'article invalide.");
}

if (!isset($_POST['open_edit']) && !isset($_POST['action'])) {
    die("Accès refusé : cette page est accessible en POST depuis Detail.");
}

$currentUserId = intval($_SESSION['user_id']);

$stmtCurrentUser = $mysqli->prepare("SELECT role FROM user WHERE id = ? LIMIT 1");
if ($stmtCurrentUser === false) {
    die("Erreur SQL (prepare current user) : " . $mysqli->error);
}
$stmtCurrentUser->bind_param("i", $currentUserId);
$stmtCurrentUser->execute();
$resultCurrentUser = $stmtCurrentUser->get_result();
$currentUser = $resultCurrentUser ? $resultCurrentUser->fetch_assoc() : null;
$stmtCurrentUser->close();

if (!$currentUser) {
    die("Utilisateur connecté introuvable.");
}

$stmtSelect = $mysqli->prepare("SELECT id, nom, description, prix, date_publication, image_url, auteur_id FROM article WHERE id = ? LIMIT 1");

if ($stmtSelect === false) {
    die("Erreur SQL (prepare select) : " . $mysqli->error);
}

$stmtSelect->bind_param("i", $id);
$stmtSelect->execute();
$result = $stmtSelect->get_result();

if ($result && $result->num_rows === 1) {
    $article = $result->fetch_assoc();
} else {
    die("Article introuvable.");
}

$stmtSelect->close();

$isAdmin = strtolower((string)($currentUser['role'] ?? '')) === 'admin';
$isOwner = intval($article['auteur_id']) === $currentUserId;

if (!$isAdmin && !$isOwner) {
    die("Accès refusé : vous ne pouvez modifier que vos articles.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update') {
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $prix = floatval($_POST['prix'] ?? 0);
        $date_publication = trim($_POST['date_publication'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');

        $stmtUpdate = $mysqli->prepare("UPDATE article SET nom = ?, description = ?, prix = ?, date_publication = ?, image_url = ? WHERE id = ?");

        if ($stmtUpdate === false) {
            $message = "Erreur SQL (prepare update) : " . $mysqli->error;
        } else {
            $stmtUpdate->bind_param("ssdssi", $nom, $description, $prix, $date_publication, $image_url, $id);

            if ($stmtUpdate->execute()) {
                $message = "Article modifié avec succès.";
            } else {
                $message = "Erreur lors de la modification : " . $stmtUpdate->error;
            }

            $stmtUpdate->close();
        }
    }

    if ($action === 'delete') {
        $mysqli->begin_transaction();

        try {
            $stmtDeleteCart = $mysqli->prepare("DELETE FROM cart WHERE article_id = ?");
            if ($stmtDeleteCart) {
                $stmtDeleteCart->bind_param("i", $id);
                $stmtDeleteCart->execute();
                $stmtDeleteCart->close();
            }

            $stmtDeleteStock = $mysqli->prepare("DELETE FROM stock WHERE article_id = ?");
            if ($stmtDeleteStock) {
                $stmtDeleteStock->bind_param("i", $id);
                $stmtDeleteStock->execute();
                $stmtDeleteStock->close();
            }

            $stmtDeleteArticle = $mysqli->prepare("DELETE FROM article WHERE id = ? LIMIT 1");
            if ($stmtDeleteArticle === false) {
                throw new Exception("Erreur SQL (prepare delete article) : " . $mysqli->error);
            }
            $stmtDeleteArticle->bind_param("i", $id);
            if (!$stmtDeleteArticle->execute()) {
                throw new Exception("Erreur suppression article : " . $stmtDeleteArticle->error);
            }
            $stmtDeleteArticle->close();

            $mysqli->commit();
            header("Location: /php_exam/groupe6__projet_php/");
            exit;
        } catch (Exception $e) {
            $mysqli->rollback();
            $message = $e->getMessage();
        }
    }
}

$stmtRefresh = $mysqli->prepare("SELECT id, nom, description, prix, date_publication, image_url, auteur_id FROM article WHERE id = ? LIMIT 1");
if ($stmtRefresh) {
    $stmtRefresh->bind_param("i", $id);
    $stmtRefresh->execute();
    $resultRefresh = $stmtRefresh->get_result();
    if ($resultRefresh && $resultRefresh->num_rows === 1) {
        $article = $resultRefresh->fetch_assoc();
    }
    $stmtRefresh->close();
}

$dateInputValue = !empty($article['date_publication']) ? substr($article['date_publication'], 0, 10) : '';
?>
<!DOCTYPE html>
<html>
<body>

<h1>Modifier l'article</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<form method="POST" action="Modifier.php">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="action" value="update">

    <label>Nom :</label><br>
    <input type="text" name="nom" required value="<?php echo htmlspecialchars($article['nom']); ?>"><br><br>

    <label>Description :</label><br>
    <textarea name="description" rows="4" cols="50" required><?php echo htmlspecialchars($article['description']); ?></textarea><br><br>

    <label>Prix :</label><br>
    <input type="number" name="prix" step="0.01" min="0" required value="<?php echo htmlspecialchars($article['prix']); ?>"><br><br>

    <label>Date de publication :</label><br>
    <input type="date" name="date_publication" required value="<?php echo htmlspecialchars($dateInputValue); ?>"><br><br>

    <label>Image URL :</label><br>
    <input type="text" name="image_url" value="<?php echo htmlspecialchars($article['image_url']); ?>"><br><br>

    <button type="submit">Modifier</button>
</form>

<form method="POST" action="Modifier.php" onsubmit="return confirm('Supprimer cet article ?');">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="action" value="delete">
    <button type="submit">Supprimer</button>
</form>

</body>
</html>