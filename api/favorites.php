<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Vérifier si l'utilisateur est connecté pour toutes les opérations
    if (!isLoggedIn()) {
        throw new Exception('Vous devez être connecté pour gérer vos favoris', 401);
    }
    
    $db = getDBConnection();
    $user_id = $_SESSION['user_id'];
    
    switch ($method) {
        case 'GET':
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? min(20, max(1, (int)$_GET['limit'])) : 12;
            $offset = ($page - 1) * $limit;
            
            // Compter le total
            $count_sql = "SELECT COUNT(*) as total FROM favorites WHERE user_id = ?";
            $count_stmt = $db->prepare($count_sql);
            $count_stmt->execute([$user_id]);
            $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Récupérer les favoris avec les détails des produits
            $sql = "SELECT f.*, p.id as product_id, p.nom as product_name, p.description, p.prix, p.image, p.status,
                           c.nom as category_name
                    FROM favorites f
                    JOIN products p ON f.product_id = p.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE f.user_id = ?
                    ORDER BY f.created_at DESC
                    LIMIT {$limit} OFFSET {$offset}";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$user_id]);
            $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $formatted_favorites = array_map(function($fav) {
                return [
                    'id' => (int)$fav['id'],
                    'product_id' => (int)$fav['product_id'],
                    'product_name' => $fav['product_name'],
                    'product_description' => $fav['description'],
                    'product_price' => (float)$fav['prix'],
                    'product_image' => $fav['image'] ? $fav['image'] : null,
                    'product_status' => $fav['status'],
                    'category_name' => $fav['category_name'],
                    'added_at' => $fav['created_at']
                ];
            }, $favorites);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'favorites' => $formatted_favorites,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => ceil($total / $limit),
                        'total_items' => (int)$total,
                        'items_per_page' => $limit
                    ]
                ]
            ]);
            break;
            
        case 'POST':
            // Ajouter un produit aux favoris
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['product_id']) || !is_numeric($input['product_id'])) {
                throw new Exception('ID du produit requis', 400);
            }
            
            $product_id = (int)$input['product_id'];
            
            // Vérifier si le produit existe
            $product_check = $db->prepare("SELECT id FROM products WHERE id = ? AND status = 'active'");
            $product_check->execute([$product_id]);
            if (!$product_check->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception('Produit non trouvé ou indisponible', 404);
            }
            
            // Vérifier si déjà en favoris
            $existing_check = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
            $existing_check->execute([$user_id, $product_id]);
            if ($existing_check->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception('Produit déjà dans vos favoris', 409);
            }
            
            // Ajouter aux favoris
            $insert_sql = "INSERT INTO favorites (user_id, product_id, created_at) VALUES (?, ?, NOW())";
            $insert_stmt = $db->prepare($insert_sql);
            $insert_stmt->execute([$user_id, $product_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Produit ajouté aux favoris',
                'favorite_id' => $db->lastInsertId()
            ]);
            break;
            
        case 'DELETE':
            // Supprimer un produit des favoris
            $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
            
            if ($product_id <= 0) {
                throw new Exception('ID du produit requis', 400);
            }
            
            $delete_sql = "DELETE FROM favorites WHERE user_id = ? AND product_id = ?";
            $delete_stmt = $db->prepare($delete_sql);
            $delete_stmt->execute([$user_id, $product_id]);
            
            if ($delete_stmt->rowCount() === 0) {
                throw new Exception('Favori non trouvé', 404);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Produit retiré des favoris'
            ]);
            break;
            
        default:
            throw new Exception('Méthode non autorisée', 405);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
