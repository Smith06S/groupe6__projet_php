<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

start_session();

if (!empty($_SESSION['user_id'])) {
    header("Location: /php_exam/groupe6__projet_php/");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $mail = trim($_POST['mail'] ?? '');
    $passwordRaw = $_POST['password'] ?? '';
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    if ($username === '' || $mail === '' || $passwordRaw === '') {
        $message = "Merci de remplir tous les champs.";
    } else {
        $stmtExists = $mysqli->prepare("SELECT id FROM user WHERE username = ? OR mail = ? LIMIT 1");

        if ($stmtExists === false) {
            $message = "Erreur SQL (prepare check) : " . $mysqli->error;
        } else {
            $stmtExists->bind_param("ss", $username, $mail);
            $stmtExists->execute();
            $stmtExists->store_result();

            if ($stmtExists->num_rows > 0) {
                $message = "Username ou email déjà utilisé.";
            } else {
                $stmt = $mysqli->prepare("INSERT INTO user (username, mail, mdp) VALUES (?, ?, ?)");

                if ($stmt === false) {
                    $message = "Erreur SQL (prepare insert) : " . $mysqli->error;
                } else {
                    $stmt->bind_param("sss", $username, $mail, $password);

                    if ($stmt->execute()) {
                        $_SESSION['user_id'] = $stmt->insert_id;
                        $_SESSION['username'] = $username;
                        header("Location: /php_exam/groupe6__projet_php/");
                        exit;
                    } else {
                        if ($stmt->errno === 1062) {
                            $message = "Username ou email déjà utilisé.";
                        } else {
                            $message = "Erreur : " . $stmt->error;
                        }
                    }

                    $stmt->close();
                }
            }

            $stmtExists->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<body>

<h1>Créer un utilisateur</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<form method="POST">

    <label>Username :</label><br>
    <input type="text" name="username" required><br><br>

    <label>Mail :</label><br>
    <input type="email" name="mail" required><br><br>

    <label>Password :</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Créer</button>
</form>

</body>
</html>