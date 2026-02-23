<?php
require_once dirname(__DIR__, 2) . "/auth.php";
require_once dirname(__DIR__, 2) . "/db.php";

start_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $mysqli->prepare("SELECT id, mdp FROM user WHERE username = ? LIMIT 1");

    if ($stmt === false) {
        $message = "Erreur SQL (prepare) : " . $mysqli->error;
    } else {
        $stmt->bind_param("s", $username);

        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($userId, $hashedPassword);
                $stmt->fetch();

                if (password_verify($password, $hashedPassword)) {
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $username;
                    header("Location: /php_exam/groupe6__projet_php/");
                    exit;
                } else {
                    $message = "Nom d'utilisateur ou mot de passe incorrect.";
                }
            } else {
                $message = "Nom d'utilisateur ou mot de passe incorrect.";
            }
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

<h1>Connexion</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo $message; ?></strong></p>
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