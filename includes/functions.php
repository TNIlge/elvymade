<?php
/**
 * Fonctions utilitaires pour ElvyMade
 * Fonctions communes utilisées dans tout le site
 */

/**
 * Génère un token CSRF pour sécuriser les formulaires
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie la validité du token CSRF
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}





/**
 * Upload d'une image avec validation
 * @param array $file Fichier uploadé ($_FILES)
 * @param string $destination Dossier de destination
 * @return array Résultat de l'upload
 */
function uploadImage($file, $destination = UPLOAD_PATH) {
    $result = ['success' => false, 'message' => '', 'filename' => ''];
    
    // Vérification des erreurs
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['message'] = 'Erreur lors de l\'upload du fichier.';
        return $result;
    }
    
    // Vérification de la taille
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        $result['message'] = 'Le fichier est trop volumineux. Taille maximale : ' . (MAX_UPLOAD_SIZE / 1024 / 1024) . 'MB';
        return $result;
    }
    
    // Vérification du type de fichier
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, ALLOWED_IMAGE_TYPES)) {
        $result['message'] = 'Type de fichier non autorisé. Types acceptés : ' . implode(', ', ALLOWED_IMAGE_TYPES);
        return $result;
    }
    
    // Génération d'un nom unique
    $filename = uniqid() . '_' . time() . '.' . $fileExtension;
    $filepath = $destination . $filename;
    
    // Création du dossier si nécessaire
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    // Déplacement du fichier
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $result['success'] = true;
        $result['filename'] = $filename;
        $result['message'] = 'Fichier uploadé avec succès.';
    } else {
        $result['message'] = 'Erreur lors de la sauvegarde du fichier.';
    }
    
    return $result;
}

/**
 * Génère un slug à partir d'une chaîne
 * @param string $text
 * @return string
 */
function generateSlug($text) {
    // Remplace les caractères spéciaux
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('/[^a-zA-Z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    $text = trim($text, '-');
    return strtolower($text);
}

/**
 * Formate une date en français
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDateFrench($date, $format = 'd/m/Y à H:i') {
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Tronque un texte à une longueur donnée
 * @param string $text
 * @param int $length
 * @param string $suffix
 * @return string
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Valide une adresse email
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valide un numéro de téléphone camerounais
 * @param string $phone
 * @return bool
 */
function validateCameroonPhone($phone) {
    // Format accepté : +237XXXXXXXXX ou 237XXXXXXXXX ou 6XXXXXXXX ou 2XXXXXXXX
    $pattern = '/^(\+?237)?[62][0-9]{8}$/';
    return preg_match($pattern, $phone);
}

/**
 * Génère une pagination
 * @param int $currentPage
 * @param int $totalPages
 * @param string $baseUrl
 * @return string
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<div class="pagination">';
    
    // Bouton précédent
    if ($currentPage > 1) {
        $prevPage = $currentPage - 1;
        $html .= "<a href='{$baseUrl}?page={$prevPage}' class='pagination-btn'>Précédent</a>";
    }
    
    // Numéros de pages
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $activeClass = ($i == $currentPage) ? 'active' : '';
        $html .= "<a href='{$baseUrl}?page={$i}' class='pagination-number {$activeClass}'>{$i}</a>";
    }
    
    // Bouton suivant
    if ($currentPage < $totalPages) {
        $nextPage = $currentPage + 1;
        $html .= "<a href='{$baseUrl}?page={$nextPage}' class='pagination-btn'>Suivant</a>";
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Affiche un message flash
 * @param string $type (success, error, warning, info)
 * @param string $message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Récupère et supprime le message flash
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Affiche le message flash en HTML
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        echo "<div class='alert alert-{$flash['type']}'>";
        echo "<span class='alert-message'>{$flash['message']}</span>";
        echo "<button class='alert-close' onclick='this.parentElement.remove()'>&times;</button>";
        echo "</div>";
    }
}



/**
 * Ajoute un produit aux favoris
 * @param int $userId
 * @param int $productId
 * @return bool
 */
function addToFavorites($userId, $productId) {
    if (!$userId || !$productId) return false;
    
    try {
        $db = getDBConnection();
        
        // Vérifier si déjà en favoris
        if (isProductInFavorites($userId, $productId)) {
            return false;
        }
        
        $stmt = $db->prepare("INSERT INTO favorites (user_id, product_id, created_at) VALUES (?, ?, NOW())");
        return $stmt->execute([$userId, $productId]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Supprime un produit des favoris
 * @param int $userId
 * @param int $productId
 * @return bool
 */
function removeFromFavorites($userId, $productId) {
    if (!$userId || !$productId) return false;
    
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$userId, $productId]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Obtient le nombre de messages non lus pour un utilisateur
 * @param int $userId
 * @return int
 */
function getUnreadMessagesCount($userId) {
    if (!is_numeric($userId)) {
        error_log("Invalid user ID provided to getUnreadMessagesCount: " . print_r($userId, true));
        return 0;
    }

    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM contact_messages WHERE email = (SELECT email FROM users WHERE id = ?) AND status != 'read'");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("Error in getUnreadMessagesCount: " . $e->getMessage());
        return 0;
    }
}
function isProductInFavorites($user_id, $product_id) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    return $stmt->fetch() !== false;
}

function getFavoritesCount($user_id) {
    if (empty($user_id)) {
        return 0;
    }
    
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("Erreur lors du comptage des favoris: " . $e->getMessage());
        return 0;
    }
}
?>
