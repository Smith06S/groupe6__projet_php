<?php
session_start();

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path = '/groupe6__projet_php';

$path = str_replace($base_path, '', $url);

if ($path === '') {
    $path = '/';
}

$isLoggedIn = isset($_SESSION['user_id']) && intval($_SESSION['user_id']) > 0;
$publicPaths = ['/', '/home', '/detail', '/login', '/register'];

if (!$isLoggedIn && !in_array($path, $publicPaths, true)) {
    header('Location: /php_exam/groupe6__projet_php/Vue/auth/Login.php');
    exit;
}

if ($isLoggedIn && $path === '/admin') {
    require_once __DIR__ . '/../Modele/userModele.php';
    $connectedUser = getUserById(intval($_SESSION['user_id']));
    $isAdmin = !empty($connectedUser['role']) && strtolower((string)$connectedUser['role']) === 'admin';

    if (!$isAdmin) {
        header('Location: /php_exam/groupe6__projet_php/Vue/products/Home.php');
        exit;
    }
}

switch ($path) {
    case '/':
    case '/home':
        require_once 'Controleur/productControleur.php'; // Affiche la home
        break;
    case '/login':
        require_once 'Controleur/authControleur.php'; // Logique de connexion
        break;
    case '/register':
        require_once 'Controleur/authControleur.php'; // Logique d'inscription
        break;
    case '/logout':
        require_once 'Controleur/logoutControleur.php';
        break;
    case '/sell':
        require_once 'Controleur/sellControleur.php'; // Logique de vente
        break;
    case '/detail':
        require_once 'Controleur/detailControleur.php'; // Logique de détail
        break;
    case '/cart':
        require_once 'Controleur/cartControleur.php'; // Logique du panier
        break;
    case '/cart/validate':
        require_once 'Controleur/cartControleur.php'; // Logique de validation du panier
        break;
    case '/edit':
        require_once 'Controleur/editControleur.php'; // Logique d'edition
        break;
    case '/account':
        require_once 'Controleur/accountControleur.php'; // Logique du compte
        break;
    case '/admin':
        require_once 'Controleur/adminControleur.php'; // Logique d'administration
        break;
    default:
        echo "404 - Page non trouvée";
        break;
}