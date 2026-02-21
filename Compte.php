<?php
$mysqli = new mysqli("localhost", "root", "", "php_exam_db");


if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

$result = $mysqli->query("SELECT * FROM user");

if ($result && $result->num_rows > 0) {

    $user = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<body>

<h1>Affichage d’un utilisateur</h1>

<?php if (!empty($user)) : ?>
    <p>Username : <?php echo htmlspecialchars($user['username']); ?></p>
    <p>Email : <?php echo htmlspecialchars($user['mail']); ?></p>
<?php else : ?>
    <p>Aucun utilisateur trouvé.</p>
<?php endif; ?>

</body>
</html>
