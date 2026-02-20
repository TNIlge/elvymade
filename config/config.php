<?php
/**
 * Configuration générale du site ElvyMade
 * Paramètres globaux et constantes
 */

// Informations du site
define('SITE_NAME', 'ElvyMade');
define('SITE_DESCRIPTION', 'Marketplace Camerounaise - Prospection d\'articles en ligne');
define('SITE_URL', 'https://elvymade.onrender.com/');
define('SITE_EMAIL', 'ilgetchouala@gmail.com');

// Paramètres de sécurité
define('HASH_ALGO', 'sha256');
define('SESSION_LIFETIME', 3600); // 1 heure en secondes

// Paramètres de l'application
define('ITEMS_PER_PAGE', 12);
define('MAX_UPLOAD_SIZE', 5242880); // 5MB en octets
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Chemins des dossiers
define('UPLOAD_PATH', 'uploads/products/');
define('ASSETS_PATH', 'assets/');
define('INCLUDES_PATH', 'includes/');

// Paramètres de localisation (Cameroun)
define('DEFAULT_CURRENCY', 'FCFA');
define('DEFAULT_TIMEZONE', 'Africa/Douala');
define('DEFAULT_LANGUAGE', 'fr');

// Villes principales du Cameroun
define('CAMEROON_CITIES', [
    'Douala',
    'Yaoundé',
    'Bafoussam',
    'Bamenda',
    'Garoua',
    'Maroua',
    'Ngaoundéré',
    'Bertoua',
    'Ebolowa',
    'Kribi'
]);

// Configuration de la timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// Démarrage de la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fonction pour formater les prix en FCFA
 * @param float $price
 * @return string
 */
function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' ' . DEFAULT_CURRENCY;
}

/**
 * Fonction pour sécuriser les données d'entrée
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Fonction pour rediriger vers une page
 * @param string $url
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Fonction pour vérifier si l'utilisateur est connecté
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Fonction pour vérifier si l'utilisateur est administrateur
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}
?>
