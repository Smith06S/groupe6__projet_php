<?php
session_start();

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path = '/php_exam/groupe6__projet_php';

$path = str_replace($base_path, '', $url);

if ($path === '') {
    $path = '/';
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
        session_destroy();
        header('Location: ./home');
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