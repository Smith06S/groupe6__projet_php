<?php
if (!defined('CART_VIEW_CONTEXT')) {
    if (!defined('CART_SKIP_AUTO_DISPATCH')) {
        define('CART_SKIP_AUTO_DISPATCH', true);
    }
    require_once __DIR__ . '/../../Controleur/cartControleur.php';
    showCartPage();
    exit;
}
?>
<!DOCTYPE html>
<html>
<body>

<h1>Panier</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<?php if (empty($cartItems)) : ?>
    <p>Ton panier est vide.</p>
<?php else : ?>
    <?php foreach ($cartItems as $item) : ?>
        <div>
            <p><strong><?php echo htmlspecialchars($item['nom'] ?? ''); ?></strong></p>
            <p>Description : <?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
            <p>Prix unitaire : <?php echo htmlspecialchars(number_format(floatval($item['prix'] ?? 0), 2, '.', '')); ?></p>
            <p>Quantité : <?php echo intval($item['quantity'] ?? 0); ?></p>
            <p>Sous-total : <?php echo htmlspecialchars(number_format(floatval($item['subtotal'] ?? 0), 2, '.', '')); ?></p>

            <p>
                <a href="/php_exam/groupe6__projet_php/Vue/products/Detail.php?id=<?php echo intval($item['article_id'] ?? 0); ?>">
                    Voir le détail de l'article
                </a>
            </p>

            <form method="POST" action="" onsubmit="return confirm('Supprimer cet article du panier ?');">
                <input type="hidden" name="article_id" value="<?php echo intval($item['article_id'] ?? 0); ?>">
                <input type="hidden" name="action" value="remove">
                <button type="submit">Supprimer du panier</button>
            </form>
            <hr>
        </div>
    <?php endforeach; ?>

    <h3>Total : <?php echo htmlspecialchars(number_format(floatval($total ?? 0), 2, '.', '')); ?></h3>
    <p>
        <a href="/php_exam/groupe6__projet_php/Vue/cart/Validate.php">Passer à la validation</a>
    </p>
<?php endif; ?>

</body>
</html>
