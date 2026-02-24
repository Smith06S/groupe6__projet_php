<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../Controleur/authControleur.php';
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = connection();
}
?>

<!DOCTYPE html>
<html>
<body>

<h1>Connexion</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<form method="POST">

    <label>Username :</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password :</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Connexion</button>
</form>

</body>
</html>