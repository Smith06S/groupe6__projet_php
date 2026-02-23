<?php
require_once __DIR__ . "/db.php";

$result = $mysqli->query("SELECT * FROM article ORDER BY date_publication DESC");
?>
<!DOCTYPE html>
<html>
<body>

<h1>Liste des articles</h1>

<?php
if ($result && $result->num_rows > 0) {

    while ($article = $result->fetch_assoc()) {
        echo "<div>";
        echo "<p>Nom : " . htmlspecialchars($article['nom']) . "</p>";
        echo "<p>Description : " . htmlspecialchars($article['description']) . "</p>";
        echo "<p>Prix : " . htmlspecialchars($article['prix']) . "</p>";
        echo "<p>Date de publication : " . htmlspecialchars($article['date_publication']) . "</p>";
        echo "<p>Image URL : " . htmlspecialchars($article['image_url']) . "</p>";
        echo "<p><a href=\"Detail.php?id=" . intval($article['id']) . "\">Voir le detail</a></p>";
        echo "<hr>";
        echo "</div>";
    }

} else {
    echo "<p>Aucun article trouvé.</p>";
}
?>

</body>
</html>