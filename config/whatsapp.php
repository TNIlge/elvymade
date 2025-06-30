<?php
/**
 * Configuration WhatsApp pour ElvyMade
 * Paramètres de communication via WhatsApp
 */

// Numéro WhatsApp de l'administrateur (format international sans le +)
define('WHATSAPP_NUMBER', '+237658470529'); // À modifier avec votre numéro

// Messages prédéfinis pour WhatsApp
define('WHATSAPP_GENERAL_MESSAGE', 'Bonjour ! Je suis intéressé par vos produits sur ElvyMade.');
define('WHATSAPP_PRODUCT_MESSAGE', 'Bonjour ! Je suis intéressé par ce produit : ');

// Configuration des messages par catégorie
$whatsapp_category_messages = [
    'electronique' => 'Bonjour ! Je cherche des produits électroniques sur ElvyMade.',
    'mode' => 'Bonjour ! Je suis intéressé par vos articles de mode.',
    'maison' => 'Bonjour ! Je cherche des articles pour la maison.',
    'accessoires' => 'Bonjour ! Je suis intéressé par vos accessoires.',
];

/**
 * Génère l'URL WhatsApp pour un produit spécifique
 * @param string $productName
 * @param int $productId
 * @return string
 */
function generateWhatsAppProductURL($productName, $productId) {
    $number = WHATSAPP_NUMBER;
    $message = WHATSAPP_PRODUCT_MESSAGE . $productName . " (Réf: " . $productId . ")";
    $encodedMessage = urlencode($message);
    
    return "https://wa.me/{$number}?text={$encodedMessage}";
}

/**
 * Génère l'URL WhatsApp générale
 * @return string
 */
function generateWhatsAppGeneralURL() {
    $number = WHATSAPP_NUMBER;
    $message = WHATSAPP_GENERAL_MESSAGE;
    $encodedMessage = urlencode($message);
    
    return "https://wa.me/{$number}?text={$encodedMessage}";
}

/**
 * Génère l'URL WhatsApp pour une catégorie
 * @param string $category
 * @return string
 */
function generateWhatsAppCategoryURL($category) {
    global $whatsapp_category_messages;
    
    $number = WHATSAPP_NUMBER;
    $message = isset($whatsapp_category_messages[$category]) 
        ? $whatsapp_category_messages[$category] 
        : WHATSAPP_GENERAL_MESSAGE;
    $encodedMessage = urlencode($message);
    
    return "https://wa.me/{$number}?text={$encodedMessage}";
}

/**
 * Affiche le bouton WhatsApp pour un produit
 * @param string $productName
 * @param int $productId
 * @param string $class Classes CSS additionnelles
 */
function displayWhatsAppProductButton($productName, $productId, $class = '') {
    $url = generateWhatsAppProductURL($productName, $productId);
    echo "<a href='{$url}' target='_blank' class='whatsapp-btn {$class}' title='Contacter via WhatsApp'>";
    echo "<i class='fab fa-whatsapp'></i> WhatsApp";
    echo "</a>";
}

/**
 * Affiche le bouton WhatsApp général
 * @param string $class Classes CSS additionnelles
 */
function displayWhatsAppGeneralButton($class = '') {
    $url = generateWhatsAppGeneralURL();
    echo "<a href='{$url}' target='_blank' class='whatsapp-btn {$class}' title='Nous contacter'>";
    echo "<i class='fab fa-whatsapp'></i>";
    echo "</a>";
}
?>
