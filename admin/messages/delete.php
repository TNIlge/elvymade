<?php
/**
 * Supprimer un message de contact - Administration
 * ElvyMade - Site de prospection d'articles
 */

header('Content-Type: application/json');

// Inclusion des fichiers de configuration
require_once '../../config/database.php';
require_once '../../config/config.php';
require_once '../../includes/functions.php';

session_start();

try {
    // Vérifier si l'utilisateur est administrateur
    if (!isLoggedIn() || !isAdmin()) {
        throw new Exception('Accès non autorisé', 403);
    }

    // Vérifier la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée', 405);
    }

    // Récupérer les données JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        throw new Exception('ID du message requis', 400);
    }

    $message_id = (int)$input['id'];

    // Connexion à la base de données
    $db = getDBConnection();

    // Vérifier si le message existe
    $check_stmt = $db->prepare("SELECT id FROM contact_messages WHERE id = ?");
    $check_stmt->execute([$message_id]);
    
    if (!$check_stmt->fetch()) {
        throw new Exception('Message non trouvé', 404);
    }

    // Supprimer le message
    $delete_stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ?");
    
    if ($delete_stmt->execute([$message_id])) {
        echo json_encode([
            'success' => true,
            'message' => 'Message supprimé avec succès'
        ]);
    } else {
        throw new Exception('Erreur lors de la suppression du message', 500);
    }

} catch (Exception $e) {
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 