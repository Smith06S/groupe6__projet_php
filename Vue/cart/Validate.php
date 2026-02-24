<!DOCTYPE html>
<html>
<body>
<h1>Confirmation</h1>

<?php if (!empty($message)) : ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<p>Solde actuel : <?php echo htmlspecialchars(number_format(floatval($user['solde']), 2, '.', '')); ?></p>

<?php if (empty($cartItems)) : ?>
    <p>Ton panier est vide.</p>
    <a href="/php_exam/groupe6__projet_php/cart">Back to cart</a>
<?php else : ?>
    <h2>Récapitulatif</h2>
    <?php foreach ($cartItems as $item) : ?>
        <p>
            <?php echo htmlspecialchars($item['nom']); ?> |
            Quantité: <?php echo intval($item['quantity']); ?> |
            Sous-total: <?php echo htmlspecialchars(number_format(floatval($item['subtotal']), 2, '.', '')); ?>
        </p>
    <?php endforeach; ?>

    <h3>Total commande : <?php echo htmlspecialchars(number_format($total, 2, '.', '')); ?></h3>

    <h2>Informations de facturation</h2>
    <form method="POST" action="/php_exam/groupe6__projet_php/cart/validate">
        <input type="hidden" name="validate_order" value="1">

        <label>Adresse :</label><br>
        <input type="text" name="facturation_address" required><br><br>

        <label>Ville :</label><br>
        <input type="text" name="facturation_city" required><br><br>

        <label>Code postal :</label><br>
        <input type="text" name="facturation_zip" required><br><br>

        <button type="submit">Valider la commande</button>
    </form>

    <p><a href="/php_exam/groupe6__projet_php/cart">Back to cart</a></p>
<?php endif; ?>

<?php if ($success) : ?>
    <p><a href="/php_exam/groupe6__projet_php/home">Back to home</a></p>
<?php endif; ?>
</body>
</html>
