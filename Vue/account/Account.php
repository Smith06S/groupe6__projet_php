<?php
if (!defined('ACCOUNT_VIEW_CONTEXT')) {
    require_once __DIR__ . '/../../Controleur/accountControleur.php';
    exit;
}

$user = is_array($user) ? $user : [];
$createdArticles = is_array($createdArticles) ? $createdArticles : [];
$purchasedArticles = is_array($purchasedArticles) ? $purchasedArticles : [];
$invoices = is_array($invoices) ? $invoices : [];
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
    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo intval($connectedUserId); ?>">
        <input type="hidden" name="action" value="update_profile">

        <label>Username :</label><br>
        <input type="text" name="username" required value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"><br><br>

        <label>Mail :</label><br>
        <input type="email" name="mail" required value="<?php echo htmlspecialchars($user['mail'] ?? ''); ?>"><br><br>

        <label>Photo de profil (URL) :</label><br>
        <input type="text" name="photo_profil" value="<?php echo htmlspecialchars($user['photo_profil'] ?? ''); ?>"><br><br>

        <label>Nouveau mot de passe (laisser vide pour ne pas changer) :</label><br>
        <input type="password" name="new_password"><br><br>

        <label>Role :</label><br>
        <input type="text" value="<?php echo htmlspecialchars($user['role'] ?? ''); ?>" disabled><br><br>

        <button type="submit">Modifier mes informations</button>
    </form>

    <h2>Ajouter de l'argent au solde</h2>
    <p>Solde actuel : <?php echo htmlspecialchars(number_format(floatval($user['solde'] ?? 0), 2, '.', '')); ?></p>
    <form method="POST" action="">
        <input type="hidden" name="action" value="add_money">
        <input type="number" name="amount" step="0.01" min="0.01" required>
        <button type="submit">Ajouter</button>
    </form>

    <p>
        <a href="/php_exam/groupe6__projet_php/Vue/cart/Cart.php">Voir mon panier</a>
    </p>
<?php else : ?>
    <h2>Informations du compte</h2>
    <p>Username : <?php echo htmlspecialchars($user['username'] ?? ''); ?></p>
    <p>Mail : <?php echo htmlspecialchars($user['mail'] ?? ''); ?></p>
    <p>Photo : <?php echo htmlspecialchars($user['photo_profil'] ?? ''); ?></p>
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

