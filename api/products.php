<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Récupérer un produit spécifique
                $product_id = (int)$_GET['id'];
                
                $sql = "SELECT p.*, c.name as category_name,
                               (SELECT COUNT(*) FROM favorites f WHERE f.product_id = p.id) as favorites_count
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.id = ?";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();
                
                if (!$product) {
                    throw new Exception('Produit non trouvé');
                }
                
                // Vérifier si l'utilisateur a ce produit en favoris
                $is_favorite = false;
                if (isset($_SESSION['user_id'])) {
                    $fav_check = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
                    $fav_check->execute([$_SESSION['user_id'], $product_id]);
                    $is_favorite = (bool)$fav_check->fetch();
                }
                
                // Produits similaires
                $similar_sql = "SELECT p.*, c.name as category_name 
                               FROM products p 
                               LEFT JOIN categories c ON p.category_id = c.id 
                               WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' 
                               ORDER BY RAND() 
                               LIMIT 4";
                
                $similar_stmt = $pdo->prepare($similar_sql);
                $similar_stmt->execute([$product['category_id'], $product_id]);
                $similar_products = $similar_stmt->fetchAll();
                
                $formatted_product = [
                    'id' => (int)$product['id'],
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'price' => (float)$product['price'],
                    'image' => $product['image'],
                    'category_id' => (int)$product['category_id'],
                    'category_name' => $product['category_name'],
                    'status' => $product['status'],
                    'favorites_count' => (int)$product['favorites_count'],
                    'is_favorite' => $is_favorite,
                    'whatsapp_link' => generateWhatsAppLink($product['name'], $product['price']),
                    'created_at' => $product['created_at'],
                    'similar_products' => array_map(function($p) {
                        return [
                            'id' => (int)$p['id'],
                            'name' => $p['name'],
                            'price' => (float)$p['price'],
                            'image' => $p['image'],
                            'category_name' => $p['category_name']
                        ];
                    }, $similar_products)
                ];
                
                echo json_encode([
                    'success' => true,
                    'data' => $formatted_product
                ]);
                
            } else {
                // Récupérer la liste des produits
                $category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
                $featured = isset($_GET['featured']) ? (bool)$_GET['featured'] : false;
                $latest = isset($_GET['latest']) ? (bool)$_GET['latest'] : false;
                $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 12;
                $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                $offset = ($page - 1) * $limit;
                
                $where_conditions = ["p.status = 'active'"];
                $params = [];
                
                if ($category > 0) {
                    $where_conditions[] = "p.category_id = ?";
                    $params[] = $category;
                }
                
                $where_clause = implode(' AND ', $where_conditions);
                $order_clause = "ORDER BY p.created_at DESC";
                
                if ($featured) {
                    $order_clause = "ORDER BY (SELECT COUNT(*) FROM favorites f WHERE f.product_id = p.id) DESC, p.created_at DESC";
                } elseif ($latest) {
                    $order_clause = "ORDER BY p.created_at DESC";
                }
                
                // Compter le total
                $count_sql = "SELECT COUNT(*) as total FROM products p WHERE {$where_clause}";
                $count_stmt = $pdo->prepare($count_sql);
                $count_stmt->execute($params);
                $total = $count_stmt->fetch()['total'];
                
                // Récupérer les produits
                $sql = "SELECT p.*, c.name as category_name,
                               (SELECT COUNT(*) FROM favorites f WHERE f.product_id = p.id) as favorites_count
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE {$where_clause}
                        {$order_clause}
                        LIMIT {$limit} OFFSET {$offset}";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $products = $stmt->fetchAll();
                
                $formatted_products = array_map(function($product) {
                    return [
                        'id' => (int)$product['id'],
                        'name' => $product['name'],
                        'description' => substr($product['description'], 0, 100) . '...',
                        'price' => (float)$product['price'],
                        'image' => $product['image'],
                        'category_id' => (int)$product['category_id'],
                        'category_name' => $product['category_name'],
                        'favorites_count' => (int)$product['favorites_count'],
                        'whatsapp_link' => generateWhatsAppLink($product['name'], $product['price']),
                        'created_at' => $product['created_at']
                    ];
                }, $products);
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'products' => $formatted_products,
                        'pagination' => [
                            'current_page' => $page,
                            'total_pages' => ceil($total / $limit),
                            'total_items' => (int)$total,
                            'items_per_page' => $limit
                        ]
                    ]
                ]);
            }
            break;
            
        case 'POST':
            // Créer un nouveau produit (admin seulement)
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Accès non autorisé');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            $required_fields = ['name', 'description', 'price', 'category_id'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    throw new Exception("Le champ {$field} est requis");
                }
            }
            
            $sql = "INSERT INTO products (name, description, price, category_id, image, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, 'active', NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $input['name'],
                $input['description'],
                (float)$input['price'],
                (int)$input['category_id'],
                $input['image'] ?? null
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Produit créé avec succès',
                'product_id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'PUT':
            // Mettre à jour un produit (admin seulement)
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Accès non autorisé');
            }
            
            $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($product_id <= 0) {
                throw new Exception('ID du produit requis');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            $update_fields = [];
            $params = [];
            
            if (isset($input['name'])) {
                $update_fields[] = "name = ?";
                $params[] = $input['name'];
            }
            
            if (isset($input['description'])) {
                $update_fields[] = "description = ?";
                $params[] = $input['description'];
            }
            
            if (isset($input['price'])) {
                $update_fields[] = "price = ?";
                $params[] = (float)$input['price'];
            }
            
            if (isset($input['category_id'])) {
                $update_fields[] = "category_id = ?";
                $params[] = (int)$input['category_id'];
            }
            
            if (isset($input['image'])) {
                $update_fields[] = "image = ?";
                $params[] = $input['image'];
            }
            
            if (isset($input['status'])) {
                $update_fields[] = "status = ?";
                $params[] = $input['status'];
            }
            
            if (empty($update_fields)) {
                throw new Exception('Aucun champ à mettre à jour');
            }
            
            $params[] = $product_id;
            
            $sql = "UPDATE products SET " . implode(', ', $update_fields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode([
                'success' => true,
                'message' => 'Produit mis à jour avec succès'
            ]);
            break;
            
        case 'DELETE':
            // Supprimer un produit (admin seulement)
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Accès non autorisé');
            }
            
            $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($product_id <= 0) {
                throw new Exception('ID du produit requis');
            }
            
            // Supprimer d'abord les favoris associés
            $delete_favorites = $pdo->prepare("DELETE FROM favorites WHERE product_id = ?");
            $delete_favorites->execute([$product_id]);
            
            // Supprimer le produit
            $delete_product = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $delete_product->execute([$product_id]);
            
            if ($delete_product->rowCount() === 0) {
                throw new Exception('Produit non trouvé');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Produit supprimé avec succès'
            ]);
            break;
            
        default:
            throw new Exception('Méthode non autorisée');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function generateWhatsAppLink($productName, $price) {
    $message = "Bonjour, je suis intéressé(e) par le produit : {$productName} - Prix: {$price} DH";
    return "https://wa.me/" . WHATSAPP_NUMBER . "?text=" . urlencode($message);
}
?>
