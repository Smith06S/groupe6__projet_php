<?php

// 1. Démarrage de la session (essentiel pour la connexion)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Inclusion de la base de données et des modèles/contrôleurs
// (A adapter selon tes noms de fichiers réels)
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../Controleur/authControleur.php';
require_once __DIR__ . '/../Controleur/productControleur.php';
require_once __DIR__ . '/../Controleur/cartControleur.php';
require_once __DIR__ . '/../Controleur/accountControleur.php';

// 3. Récupération de l'action demandée (par défaut 'home')
$action = $_GET['action'] ?? 'home';

// 4. Le ROUTEUR (Aiguillage MVC par fonctions)
switch ($action) {
    
    // --- AUTHENTIFICATION ---
    case 'login':
        auth_login(); // A implémenter dans ../Controleur/authControleur.php
        break;
        
    case 'register':
        auth_register(); // A implémenter dans ../Controleur/authControleur.php
        break;
        
    case 'logout':
        auth_logout(); // A implémenter dans ../Controleur/authControleur.php
        break;

    // --- PRODUITS (Accessible à tous) ---
    case 'home':
        product_index(); // A implémenter dans ../Controleur/productControleur.php
        break;
        
    case 'detail':
        $id = $_GET['id'] ?? null;
        product_show($id); // A implémenter dans ../Controleur/productControleur.php
        break;

    // --- ACTIONS CONNECTÉES (Vérifier la session dans le contrôleur) ---
    case 'sell':
        // Correspond à la page "setu" du sujet
        product_create(); // A implémenter dans ../Controleur/productControleur.php
        break;
        
    case 'edit_article':
        $id = $_GET['id'] ?? null;
        product_edit($id); // A implémenter dans ../Controleur/productControleur.php
        break;

    // --- PANIER ET FACTURES ---
    case 'cart':
        cart_view(); // A implémenter dans ../Controleur/cartControleur.php
        break;
        
    case 'add_to_cart':
        cart_add(); // A implémenter dans ../Controleur/cartControleur.php
        break;
        
    case 'validate_cart':
        // Vérifie le solde et génère la facture (validate dans le sujet)
        cart_validate(); // A implémenter dans ../Controleur/cartControleur.php
        break;

    // --- COMPTE UTILISATEUR ---
    case 'account':
        account_profile(); // A implémenter dans ../Controleur/accountControleur.php
        break;

    // --- ADMINISTRATION ---
    case 'admin':
        // Vérifier le rôle admin ici ou dans le contrôleur
        account_admin_dashboard(); // A implémenter dans ../Controleur/accountControleur.php
        break;

    // --- ERREUR 404 ---
    default:
        http_response_code(404);
        echo '<h1>404 - Page non trouvée</h1>';
        break;
}