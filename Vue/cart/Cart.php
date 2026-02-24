
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
            <p><strong><?php echo htmlspecialchars($item['nom']); ?></strong></p>
            <p>Prix unitaire : <?php echo htmlspecialchars($item['prix']); ?></p>
            <p>Quantité : <?php echo intval($item['quantity']); ?></p>
            <p>Sous-total : <?php echo htmlspecialchars($item['subtotal']); ?></p>
            <p>Image : <?php echo htmlspecialchars($item['image_url']); ?></p>

            <form method="POST" action="/php_exam/groupe6__projet_php/cart" style="display:inline-block;">
                <input type="hidden" name="article_id" value="<?php echo intval($item['article_id']); ?>">
                <input type="hidden" name="action" value="increase">
                <button type="submit">+1</button>
            </form>

            <form method="POST" action="/php_exam/groupe6__projet_php/cart" style="display:inline-block;">
                <input type="hidden" name="article_id" value="<?php echo intval($item['article_id']); ?>">
                <input type="hidden" name="action" value="decrease">
                <button type="submit">-1</button>
            </form>

            <form method="POST" action="/php_exam/groupe6__projet_php/cart" style="display:inline-block;">
                <input type="hidden" name="article_id" value="<?php echo intval($item['article_id']); ?>">
                <input type="hidden" name="action" value="remove_all">
                <button type="submit">Supprimer</button>
            </form>
            <hr>
        </div>
    <?php endforeach; ?>

    <h3>Total : <?php echo htmlspecialchars(number_format($total, 2, '.', '')); ?></h3>
    <a href="/php_exam/groupe6__projet_php/cart/validate">Passer la commande</a>
<?php endif; ?>
</body>
</html>
