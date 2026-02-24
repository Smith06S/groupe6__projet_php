
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