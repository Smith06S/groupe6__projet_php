<?php
if (!defined('CART_VALIDATE_VIEW_CONTEXT')) {
    if (!defined('CART_SKIP_AUTO_DISPATCH')) {
        define('CART_SKIP_AUTO_DISPATCH', true);
    }
    require_once __DIR__ . '/../../Controleur/cartControleur.php';
    showCartValidatePage();
    exit;
}
?>
<!DOCTYPE html>
<html>
<body>

<h1>Validation de commande</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<p>Solde actuel : <?php echo htmlspecialchars(number_format(floatval($user['solde'] ?? 0), 2, '.', '')); ?></p>

<?php if (empty($cartItems)) : ?>
    <p>Ton panier est vide.</p>
    <p><a href="/php_exam/groupe6__projet_php/Vue/cart/Cart.php">Retour au panier</a></p>
<?php else : ?>
    <h2>Récapitulatif</h2>
    <?php foreach ($cartItems as $item) : ?>
        <p>
            <?php echo htmlspecialchars($item['nom'] ?? ''); ?> |
            Quantité : <?php echo intval($item['quantity'] ?? 0); ?> |
            Sous-total : <?php echo htmlspecialchars(number_format(floatval($item['subtotal'] ?? 0), 2, '.', '')); ?>
        </p>
    <?php endforeach; ?>

    <h3>Total commande : <?php echo htmlspecialchars(number_format(floatval($total ?? 0), 2, '.', '')); ?></h3>

    <?php if (!empty($isBalanceTooLow)) : ?>
        <p><strong>Solde insuffisant pour valider la commande.</strong></p>
        <p>
            <a href="/php_exam/groupe6__projet_php/Vue/account/Account.php">Aller au compte pour ajouter de l'argent</a>
        </p>
    <?php endif; ?>

    <h2>Informations de facturation</h2>
    <?php if (empty($isBalanceTooLow)) : ?>
        <form method="POST" action="">
            <input type="hidden" name="validate_order" value="1">

            <label>Adresse :</label><br>
            <input type="text" name="facturation_address" required><br><br>

            <label>Ville :</label><br>
            <input type="text" name="facturation_city" required><br><br>

            <label>Code postal :</label><br>
            <input type="text" name="facturation_zip" required><br><br>

            <button type="submit">Valider la commande</button>
        </form>
    <?php endif; ?>

    <p><a href="/php_exam/groupe6__projet_php/Vue/cart/Cart.php">Retour au panier</a></p>
<?php endif; ?>

<?php if (!empty($success)) : ?>
    <p><a href="/php_exam/groupe6__projet_php/Vue/products/Home.php">Retour à l'accueil</a></p>
<?php endif; ?>

</body>
</html>
