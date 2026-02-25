<?php
require_once __DIR__ . '/../Modele/cartModele.php';
require_once __DIR__ . '/../Modele/invoiceModele.php';

function showCartPage() {
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$userId = intval($_SESSION['user_id'] ?? 0);
	if ($userId <= 0) {
		header('Location: /php_exam/groupe6__projet_php/Vue/auth/Login.php');
		exit;
	}

	$message = '';

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$action = $_POST['action'] ?? '';
		$articleId = intval($_POST['article_id'] ?? 0);

		if ($action === 'set_quantity' && $articleId > 0) {
			$requestedQty = intval($_POST['quantity'] ?? 1);
			if ($requestedQty < 1) {
				$requestedQty = 1;
			}

			$result = setCartArticleQuantity($userId, $articleId, $requestedQty);
			if (!empty($result['ok'])) {
				$message = 'Quantité mise à jour.';
			} else {
				$message = 'Impossible de mettre cette quantité (stock max atteint).';
			}
		}

		if ($action === 'remove' && $articleId > 0) {
			$removed = removeArticleFromCart($userId, $articleId);
			$message = $removed ? 'Article supprimé du panier.' : 'Erreur lors de la suppression.';
		}
	}

	$cartItems = getUserCartItems($userId);
	$total = 0;
	foreach ($cartItems as $index => $item) {
		$articleId = intval($item['article_id'] ?? 0);
		$cartItems[$index]['max_quantity'] = getMaxAllowedCartQuantityForArticle($userId, $articleId);

		$total += floatval($item['subtotal'] ?? 0);
	}

	if (!defined('CART_VIEW_CONTEXT')) {
		define('CART_VIEW_CONTEXT', true);
	}

	require __DIR__ . '/../Vue/cart/Cart.php';
}

function showCartValidatePage() {
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$userId = intval($_SESSION['user_id'] ?? 0);
	if ($userId <= 0) {
		header('Location: /php_exam/groupe6__projet_php/Vue/auth/Login.php');
		exit;
	}

	$message = '';
	$success = false;

	$checkoutData = getCheckoutData($userId);
	$user = $checkoutData['user'];
	$cartItems = $checkoutData['cartItems'];
	$total = floatval($checkoutData['total']);
	$isBalanceTooLow = (!empty($cartItems) && floatval($user['solde'] ?? 0) < $total);

	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['validate_order'])) {
		$billingAddress = trim($_POST['facturation_address'] ?? '');
		$billingCity = trim($_POST['facturation_city'] ?? '');
		$billingZip = trim($_POST['facturation_zip'] ?? '');

		if ($billingAddress === '' || $billingCity === '' || $billingZip === '') {
			$message = 'Merci de remplir toutes les informations de facturation.';
		} elseif ($isBalanceTooLow) {
			$message = 'Solde insuffisant pour valider la commande.';
		} else {
			$result = validateAndCreateInvoice($userId, $billingAddress, $billingCity, $billingZip);
			$success = !empty($result['success']);
			$message = strval($result['message'] ?? 'Erreur inconnue.');

			$checkoutData = getCheckoutData($userId);
			$user = $checkoutData['user'];
			$cartItems = $checkoutData['cartItems'];
			$total = floatval($checkoutData['total']);
			$isBalanceTooLow = (!empty($cartItems) && floatval($user['solde'] ?? 0) < $total);
		}
	}

	if (!defined('CART_VALIDATE_VIEW_CONTEXT')) {
		define('CART_VALIDATE_VIEW_CONTEXT', true);
	}

	require __DIR__ . '/../Vue/cart/Validate.php';
}

function dispatchCartController() {
	$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
	if (strpos($path, '/cart/validate') !== false) {
		showCartValidatePage();
		return;
	}

	showCartPage();
}

if (!defined('CART_SKIP_AUTO_DISPATCH')) {
	dispatchCartController();
}

