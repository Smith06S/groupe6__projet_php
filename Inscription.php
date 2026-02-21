<?php
$mysqli = new mysqli("localhost", "root", "", "php_exam_db");

if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'];
    $mail = $_POST['mail'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $mysqli->prepare("INSERT INTO user (username, mail, mdp) VALUES (?, ?, ?)");

    if ($stmt === false) {
        $message = "Erreur SQL (prepare) : " . $mysqli->error;
    } else {
        $stmt->bind_param("sss", $username, $mail, $password);

        if ($stmt->execute()) {
            header("Location: Home.php");
            exit;
        } else {
            $message = "Erreur : " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<body>

<h1>Créer un utilisateur</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo $message; ?></strong></p>
<?php endif; ?>

<form method="POST">

    <label>Username :</label><br>
    <input type="text" name="username" required><br><br>

    <label>Mail :</label><br>
    <input type="mail" name="mail" required><br><br>

    <label>Password :</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Créer</button>
</form>

</body>
</html>