<?php
require_once __DIR__ . '/../../Controleur/productControleur.php';
$articles = showProductsHome();
$searchTerm = trim($_GET['search'] ?? '');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$isAdminUser = false;
$connectedUserId = intval($_SESSION['user_id'] ?? 0);
if ($connectedUserId > 0) {
    $connectedUser = getUserById($connectedUserId);
    $isAdminUser = !empty($connectedUser['role']) && strtolower((string)$connectedUser['role']) === 'admin';
}
?>

<!DOCTYPE html>
<html>
<body>

<h1>Liste des articles</h1>

<form method="GET" action="">
    <label for="search">Rechercher un article :</label>
    <input
        type="text"
        id="search"
        name="search"
        value="<?= htmlspecialchars($searchTerm) ?>"
        placeholder="Nom ou description"
    >
    <button type="submit">Rechercher</button>
    <?php if ($searchTerm !== '') : ?>
        <a href="/php_exam/groupe6__projet_php/Vue/products/Home.php">Réinitialiser</a>
    <?php endif; ?>
</form>

<?php if ($isAdminUser) : ?>
    <p>
        <a href="/php_exam/groupe6__projet_php/Controleur/adminControleur.php">Accéder au panneau admin</a>
    </p>
<?php endif; ?>

<p>
    <a href="/php_exam/groupe6__projet_php/Vue/products/Sell.php">Vendre un article</a>
</p>


<?php if ($articles !== "Error"): ?>
    <?php foreach ($articles as $article): ?>
        <div>
            <p>Nom : <?= htmlspecialchars($article['nom']) ?></p>
            <p>Description : <?= htmlspecialchars($article['description']) ?></p>
            <p>Prix : <?= htmlspecialchars($article['prix']) ?></p>
            <p>Date de publication : <?= htmlspecialchars($article['date_publication']) ?></p>
            <p>Image URL : <?= htmlspecialchars($article['image_url']) ?></p>
            <p>
                <a href="/php_exam/groupe6__projet_php/Vue/products/Detail.php?id=<?= intval($article['id']) ?>">
                    Ouvrir le détail de l'article
                </a>
            </p>
            <hr>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>
        <?= $searchTerm !== '' ? 'Aucun article trouvé pour votre recherche.' : 'Aucun article trouvé.' ?>
    </p>
<?php endif; ?>

</body>
</html>