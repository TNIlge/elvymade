<?php
/**
 * API pour la gestion des utilisateurs
 * ElvyMade - Site de prospection d'articles
 */

// Headers pour l'API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Inclusion des fichiers de configuration
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';

// Démarrage de la session
session_start();

// Vérifier si l'utilisateur est connecté et administrateur
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

// Connexion à la base de données
$db = getDBConnection();

// Traitement des requêtes
$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'POST':
            handlePostRequest($db, $action);
            break;
        case 'GET':
            handleGetRequest($db, $action);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}

/**
 * Traite les requêtes POST
 */
function handlePostRequest($db, $action) {
    switch ($action) {
        case 'change_role':
            changeUserRole($db);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
    }
}

/**
 * Traite les requêtes GET
 */
function handleGetRequest($db, $action) {
    switch ($action) {
        case 'list':
            getUsersList($db);
            break;
        case 'stats':
            getUsersStats($db);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
    }
}

/**
 * Change le rôle d'un utilisateur
 */
function changeUserRole($db) {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $new_role = $_POST['new_role'] ?? '';
    
    // Validation
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID utilisateur invalide']);
        return;
    }
    
    if (!in_array($new_role, ['user', 'admin'])) {
        echo json_encode(['success' => false, 'message' => 'Rôle invalide']);
        return;
    }
    
    // Vérifier que l'utilisateur existe
    $stmt = $db->prepare("SELECT id, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
        return;
    }
    
    // Empêcher de modifier son propre rôle
    if ($user_id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas modifier votre propre rôle']);
        return;
    }
    
    // Mettre à jour le rôle
    $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
    
    if ($stmt->execute([$new_role, $user_id])) {
        // Log de l'action
        logAction('change_user_role', "Rôle de l'utilisateur $user_id changé de {$user['role']} à $new_role");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Rôle modifié avec succès',
            'new_role' => $new_role
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification du rôle']);
    }
}

/**
 * Récupère la liste des utilisateurs
 */
function getUsersList($db) {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(100, (int)($_GET['limit'] ?? 20)));
    $search = $_GET['search'] ?? '';
    $role = $_GET['role'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    // Construction de la requête
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(nom LIKE ? OR prenom LIKE ? OR email LIKE ?)";
        $search_term = '%' . $search . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if (!empty($role)) {
        $where_conditions[] = "role = ?";
        $params[] = $role;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Compter le total
    $count_sql = "SELECT COUNT(*) FROM users $where_clause";
    $count_stmt = $db->prepare($count_sql);
    $count_stmt->execute($params);
    $total = $count_stmt->fetchColumn();
    
    // Récupérer les utilisateurs
    $sql = "
        SELECT id, nom, prenom, email, telephone, ville, role, created_at 
        FROM users 
        $where_clause 
        ORDER BY created_at DESC 
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $users,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_items' => $total,
            'items_per_page' => $limit
        ]
    ]);
}

/**
 * Récupère les statistiques des utilisateurs
 */
function getUsersStats($db) {
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
            SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as users_count,
            SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as recent_week,
            SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as recent_month
        FROM users
    ");
    
    $stats = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
}
/**
 * Log an action in the system (simple implementation).
 */
function logAction($action, $description) {
    // Example: log to a file (you can adapt to your needs)
    $logFile = __DIR__ . '/../logs/actions.log';
    $date = date('Y-m-d H:i:s');
    $userId = $_SESSION['user_id'] ?? 'unknown';
    $entry = "[$date] [user:$userId] [$action] $description" . PHP_EOL;
    file_put_contents($logFile, $entry, FILE_APPEND);
}
?>
