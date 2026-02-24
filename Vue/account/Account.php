<?php
require_once dirname(__DIR__, 2) . "/auth.php";
require_once dirname(__DIR__, 2) . "/db.php";

require_login();

$message = "";
$connectedUserId = intval($_SESSION['user_id']);
$targetUserId = intval($_GET['id'] ?? $_POST['id'] ?? $connectedUserId);

if ($targetUserId <= 0) {
    die("Invalid user. Open this page with an id, for example: /account?id=1");
}

$isOwnProfile = ($targetUserId === $connectedUserId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (!$isOwnProfile) {
        $message = "Vous ne pouvez pas modifier le compte d'un autre utilisateur.";
    } else {
        if ($action === 'update_profile') {
            $username = trim($_POST['username'] ?? '');
            $mail = trim($_POST['mail'] ?? '');
            $photo_profil = trim($_POST['photo_profil'] ?? '');
            $newPassword = trim($_POST['new_password'] ?? '');

            if ($username === '' || $mail === '') {
                $message = "Username et mail sont obligatoires.";
            } else {
                $stmtCheckUnique = $mysqli->prepare("SELECT id FROM user WHERE (username = ? OR mail = ?) AND id <> ? LIMIT 1");

                if ($stmtCheckUnique === false) {
                    $message = "Erreur SQL (prepare check unique) : " . $mysqli->error;
                } else {
                    $stmtCheckUnique->bind_param("ssi", $username, $mail, $connectedUserId);
                    $stmtCheckUnique->execute();
                    $stmtCheckUnique->store_result();

                    if ($stmtCheckUnique->num_rows > 0) {
                        $message = "Username ou email déjà utilisé.";
                    } else {
                        if ($newPassword !== '') {
                            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                            $stmtUpdate = $mysqli->prepare("UPDATE user SET username = ?, mail = ?, photo_profil = ?, mdp = ? WHERE id = ?");

                            if ($stmtUpdate === false) {
                                $message = "Erreur SQL (prepare update) : " . $mysqli->error;
                            } else {
                                $stmtUpdate->bind_param("ssssi", $username, $mail, $photo_profil, $hashedPassword, $connectedUserId);
                            }
                        } else {
                            $stmtUpdate = $mysqli->prepare("UPDATE user SET username = ?, mail = ?, photo_profil = ? WHERE id = ?");

                            if ($stmtUpdate === false) {
                                $message = "Erreur SQL (prepare update) : " . $mysqli->error;
                            } else {
                                $stmtUpdate->bind_param("sssi", $username, $mail, $photo_profil, $connectedUserId);
                            }
                        }

                        if (!empty($stmtUpdate)) {
                            if ($stmtUpdate->execute()) {
                                $_SESSION['username'] = $username;
                                $message = "Informations utilisateur mises a jour.";
                            } else {
                                $message = "Erreur lors de la mise a jour : " . $stmtUpdate->error;
                            }

                            $stmtUpdate->close();
                        }
                    }

                    $stmtCheckUnique->close();
                }
            }
        }

        if ($action === 'add_money') {
            $amount = floatval($_POST['amount'] ?? 0);

            if ($amount <= 0) {
                $message = "Le montant ajoute doit etre superieur a 0.";
            } else {
                $stmtAddMoney = $mysqli->prepare("UPDATE user SET solde = solde + ? WHERE id = ?");
                if ($stmtAddMoney === false) {
                    $message = "Erreur SQL (prepare add money) : " . $mysqli->error;
                } else {
                    $stmtAddMoney->bind_param("di", $amount, $connectedUserId);
                    if ($stmtAddMoney->execute()) {
                        $message = "Solde mis a jour avec succes.";
                    } else {
                        $message = "Erreur lors de l'ajout d'argent : " . $stmtAddMoney->error;
                    }
                    $stmtAddMoney->close();
                }
            }
        }
    }
}

$stmtUser = $mysqli->prepare("SELECT id, username, mail, solde, photo_profil, role FROM user WHERE id = ? LIMIT 1");

if ($stmtUser === false) {
    die("Erreur SQL (prepare user) : " . $mysqli->error);
}

$stmtUser->bind_param("i", $targetUserId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser ? $resultUser->fetch_assoc() : null;
$stmtUser->close();

if (!$user) {
    die("Utilisateur introuvable.");
}

$createdArticles = [];
$stmtCreated = $mysqli->prepare("SELECT id, nom, description, prix, date_publication, image_url FROM article WHERE auteur_id = ? ORDER BY date_publication DESC");

if ($stmtCreated) {
    $stmtCreated->bind_param("i", $targetUserId);
    $stmtCreated->execute();
    $resultCreated = $stmtCreated->get_result();
    if ($resultCreated) {
        $createdArticles = $resultCreated->fetch_all(MYSQLI_ASSOC);
    }
    $stmtCreated->close();
}

$purchasedArticles = [];
if ($isOwnProfile) {
    $stmtPurchased = $mysqli->prepare("SELECT a.id, a.nom, a.description, ii.quantity, ii.price, i.transaction_date
                                      FROM invoice_item ii
                                      INNER JOIN invoice i ON i.id = ii.invoice_id
                                      INNER JOIN article a ON a.id = ii.article_id
                                      WHERE i.user_id = ?
                                      ORDER BY i.transaction_date DESC, a.id DESC");
    if ($stmtPurchased) {
        $stmtPurchased->bind_param("i", $connectedUserId);
        $stmtPurchased->execute();
        $resultPurchased = $stmtPurchased->get_result();
        if ($resultPurchased) {
            $purchasedArticles = $resultPurchased->fetch_all(MYSQLI_ASSOC);
        }
        $stmtPurchased->close();
    }
}

$invoices = [];
if ($isOwnProfile) {
    $stmtInvoices = $mysqli->prepare("SELECT id, transaction_date, montant, facturation_address, facturation_city, facturation_zip
                                      FROM invoice
                                      WHERE user_id = ?
                                      ORDER BY transaction_date DESC");
    if ($stmtInvoices) {
        $stmtInvoices->bind_param("i", $connectedUserId);
        $stmtInvoices->execute();
        $resultInvoices = $stmtInvoices->get_result();
        if ($resultInvoices) {
            $invoices = $resultInvoices->fetch_all(MYSQLI_ASSOC);
        }
        $stmtInvoices->close();
    }
}
?>
<!DOCTYPE html>
<html>
<body>

<h1>Compte utilisateur</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<?php if ($isOwnProfile) : ?>
    <h2>Mes informations (modifiable)</h2>
    <form method="POST" action="/php_exam/groupe6__projet_php/account">
        <input type="hidden" name="id" value="<?php echo intval($connectedUserId); ?>">
        <input type="hidden" name="action" value="update_profile">

        <label>Username :</label><br>
        <input type="text" name="username" required value="<?php echo htmlspecialchars($user['username']); ?>"><br><br>

        <label>Mail :</label><br>
        <input type="email" name="mail" required value="<?php echo htmlspecialchars($user['mail']); ?>"><br><br>

        <label>Photo de profil (URL) :</label><br>
        <input type="text" name="photo_profil" value="<?php echo htmlspecialchars($user['photo_profil']); ?>"><br><br>

        <label>Nouveau mot de passe (laisser vide pour ne pas changer) :</label><br>
        <input type="password" name="new_password"><br><br>

        <label>Role :</label><br>
        <input type="text" value="<?php echo htmlspecialchars($user['role']); ?>" disabled><br><br>

        <button type="submit">Modifier mes informations</button>
    </form>

    <h2>Ajouter de l'argent au solde</h2>
    <p>Solde actuel : <?php echo htmlspecialchars(number_format(floatval($user['solde']), 2, '.', '')); ?></p>
    <form method="POST" action="/php_exam/groupe6__projet_php/account">
        <input type="hidden" name="action" value="add_money">
        <input type="number" name="amount" step="0.01" min="0.01" required>
        <button type="submit">Ajouter</button>
    </form>
<?php else : ?>
    <h2>Informations du compte</h2>
    <p>Username : <?php echo htmlspecialchars($user['username']); ?></p>
    <p>Mail : <?php echo htmlspecialchars($user['mail']); ?></p>
    <p>Photo : <?php echo htmlspecialchars($user['photo_profil']); ?></p>
<?php endif; ?>

<hr>

<h2>Articles publies par ce compte</h2>
<?php if (!empty($createdArticles)) : ?>
    <?php foreach ($createdArticles as $article) : ?>
        <div>
            <p><strong><?php echo htmlspecialchars($article['nom']); ?></strong></p>
            <p><?php echo htmlspecialchars($article['description']); ?></p>
            <p>Prix : <?php echo htmlspecialchars($article['prix']); ?></p>
            <p>Date : <?php echo htmlspecialchars($article['date_publication']); ?></p>
            <p>Image : <?php echo htmlspecialchars($article['image_url']); ?></p>
            <hr>
        </div>
    <?php endforeach; ?>
<?php else : ?>
    <p>Aucun article publie.</p>
<?php endif; ?>

<?php if ($isOwnProfile) : ?>
    <h2>Articles achetes</h2>
    <?php if (!empty($purchasedArticles)) : ?>
        <?php foreach ($purchasedArticles as $article) : ?>
            <div>
                <p><strong><?php echo htmlspecialchars($article['nom']); ?></strong></p>
                <p><?php echo htmlspecialchars($article['description']); ?></p>
                <p>Quantite : <?php echo intval($article['quantity']); ?></p>
                <p>Prix unitaire : <?php echo htmlspecialchars(number_format(floatval($article['price']), 2, '.', '')); ?></p>
                <p>Date : <?php echo htmlspecialchars($article['transaction_date']); ?></p>
                <hr>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>Aucun achat enregistre.</p>
    <?php endif; ?>

    <h2>Mes factures</h2>
    <?php if (!empty($invoices)) : ?>
        <?php foreach ($invoices as $invoice) : ?>
            <div>
                <p><strong>Facture #<?php echo intval($invoice['id']); ?></strong></p>
                <p>Date : <?php echo htmlspecialchars($invoice['transaction_date']); ?></p>
                <p>Montant : <?php echo htmlspecialchars(number_format(floatval($invoice['montant']), 2, '.', '')); ?></p>
                <p>Adresse : <?php echo htmlspecialchars($invoice['facturation_address']); ?></p>
                <p>Ville : <?php echo htmlspecialchars($invoice['facturation_city']); ?></p>
                <p>Code postal : <?php echo htmlspecialchars($invoice['facturation_zip']); ?></p>
                <hr>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>Aucune facture disponible.</p>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>

