<?php
/**
 * Fonctions utilitaires pour l'administration
 * ElvyMade - Site de prospection d'articles
 */

/**
 * Inclure les assets CSS pour l'administration
 */
function includeAdminAssets($level = 0) {
    $prefix = str_repeat('../', $level);
    echo '<link rel="stylesheet" href="' . $prefix . 'assets/css/style.css">';
    echo '<link rel="stylesheet" href="' . $prefix . 'assets/css/admin.css">';
    echo '<link rel="stylesheet" href="' . $prefix . 'assets/css/responsive.css">';
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';
}

/**
 * Inclure les scripts JS pour l'administration
 */
function includeAdminScripts($level = 0) {
    $prefix = str_repeat('../', $level);
    echo '<script src="' . $prefix . 'assets/js/admin.js"></script>';
}

// /**
//  * Obtenir le nombre de messages non lus
//  */
// function getUnreadMessagesCount($db) {
//     try {
//         $stmt = $db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'");
//         return $stmt->fetchColumn();
//     } catch (Exception $e) {
//         return 0;
//     }
// }

/**
 * Générer le layout de base pour une page admin
 */
function renderAdminLayout($content, $page_title = 'Administration', $page_icon = 'fas fa-cog', $header_actions = '', $level = 0) {
    global $unread_messages;
    
    $prefix = str_repeat('../', $level);
    
    echo '<!DOCTYPE html>';
    echo '<html lang="fr">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . htmlspecialchars($page_title) . ' - Administration ElvyMade</title>';
    includeAdminAssets($level);
    echo '</head>';
    echo '<body>';
    echo '<div class="admin-layout">';
    
    // Variables pour les includes
    $GLOBALS['page_title'] = $page_title;
    $GLOBALS['page_icon'] = $page_icon;
    $GLOBALS['header_actions'] = $header_actions;
    
    include $prefix . 'includes/sidebar.php';
    
    echo '<main class="admin-main">';
    include $prefix . 'includes/header.php';
    echo '<div class="admin-content">';
    echo $content;
    echo '</div>';
    echo '</main>';
    echo '</div>';
    
    includeAdminScripts($level);
    echo '</body>';
    echo '</html>';
}
