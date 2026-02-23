<?php
$requestUriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$basePath = rtrim($scriptDirectory, '/');

if ($basePath !== '' && $basePath !== '/' && str_starts_with($requestUriPath, $basePath)) {
	$requestUriPath = substr($requestUriPath, strlen($basePath));
}

$routePath = '/' . ltrim($requestUriPath, '/');
$routePath = rtrim($routePath, '/');
if ($routePath === '') {
	$routePath = '/';
}

$routes = [
	'/' => __DIR__ . '/../Vue/products/Home.php',
	'/index.php' => __DIR__ . '/../Vue/products/Home.php',
	'/home' => __DIR__ . '/../Vue/products/Home.php',
	'/Home.php' => __DIR__ . '/../Vue/products/Home.php',

	'/login' => __DIR__ . '/../Vue/auth/Login.php',
	'/Login.php' => __DIR__ . '/../Vue/auth/Login.php',

	'/register' => __DIR__ . '/../Vue/auth/Register.php',
	'/Register.php' => __DIR__ . '/../Vue/auth/Register.php',

	'/detail' => __DIR__ . '/../Vue/products/Detail.php',
	'/Detail.php' => __DIR__ . '/../Vue/products/Detail.php',

	'/edit' => __DIR__ . '/../Vue/products/Edit.php',
	'/Edit.php' => __DIR__ . '/../Vue/products/Edit.php',

	'/sell' => __DIR__ . '/../Vue/products/Sell.php',
	'/Sell.php' => __DIR__ . '/../Vue/products/Sell.php',

	'/cart' => __DIR__ . '/../Vue/Cart/Panier.php',
	'/Cart.php' => __DIR__ . '/../Vue/Cart/Panier.php',

	'/cart/validate' => __DIR__ . '/../Vue/Cart/Confirmation.php',
	'/Confirmation.php' => __DIR__ . '/../Vue/Cart/Confirmation.php',

	'/admin' => __DIR__ . '/../Vue/admin/Admin.php',
	'/Admin.php' => __DIR__ . '/../Vue/admin/Admin.php',

	'/account' => __DIR__ . '/../Vue/account/Account.php',
	'/Account.php' => __DIR__ . '/../Vue/account/Account.php',
];

if (!isset($routes[$routePath])) {
	http_response_code(404);
	echo '<h1>404 - Page non trouvée</h1>';
	exit;
}

require_once $routes[$routePath];
