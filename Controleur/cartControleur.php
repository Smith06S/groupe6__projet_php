<?php
function cart_view() : void
{
    $message = '';
    $cartItems = [];
    $total = 0.0;

    require_once __DIR__ . '/../Vue/cart/Cart.php';
}

function cart_add() : void
{
    $_SESSION['error'] = 'Ajout au panier non implémenté dans ce contrôleur.';
    header('Location: /php_exam/groupe6__projet_php/cart');
    exit;
}

function cart_validate() : void
{
    $message = '';
    $cartItems = [];
    $total = 0.0;
    $success = false;
    $user = ['solde' => 0];

    require_once __DIR__ . '/../Vue/cart/Validate.php';
}