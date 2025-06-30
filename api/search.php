<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../includes/functions.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        $query = isset($_GET['q']) ? trim($_GET['q']) : '';
        $category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
        $min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
        $max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
        $order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 12;
        $offset = ($page - 1) * $limit;
        
        // Validation du tri
        $allowed_sorts = ['name', 'price', 'created_at', 'category_name'];
        $sort = in_array($sort, $allowed_sorts) ? $sort : 'name';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        
        // Construction de la requête
        $where_conditions = ["p.status = 'active'"];
        $params = [];
        
        if (!empty($query)) {
            $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
            $search_term = "%{$query}%";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        if ($category > 0) {
            $where_conditions[] = "p.category_id = ?";
            $params[] = $category;
        }
        
        if ($min_price > 0) {
            $where_conditions[] = "p.price >= ?";
            $params[] = $min_price;
        }
        
        if ($max_price > 0) {
            $where_conditions[] = "p.price <= ?";
            $params[] = $max_price;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Requête pour compter le total
        $count_sql = "SELECT COUNT(*) as total 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      WHERE {$where_clause}";
        
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total = $count_stmt->fetch()['total'];
        
        // Requête principale
        $sql = "SELECT p.*, c.name as category_name,
                       (SELECT COUNT(*) FROM favorites f WHERE f.product_id = p.id) as favorites_count
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE {$where_clause}
                ORDER BY {$sort} {$order}
                LIMIT {$limit} OFFSET {$offset}";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
        // Formatage des résultats
        $formatted_products = array_map(function($product) {
            return [
                'id' => (int)$product['id'],
                'name' => $product['name'],
                'description' => $product['description'],
                'price' => (float)$product['price'],
                'image' => $product['image'],
                'category_id' => (int)$product['category_id'],
                'category_name' => $product['category_name'],
                'favorites_count' => (int)$product['favorites_count'],
                'whatsapp_link' => generateWhatsAppLink($product['name'], $product['price']),
                'created_at' => $product['created_at']
            ];
        }, $products);
        
        // Suggestions de recherche si aucun résultat
        $suggestions = [];
        if (empty($formatted_products) && !empty($query)) {
            $suggestion_sql = "SELECT DISTINCT name FROM products 
                              WHERE name LIKE ? AND status = 'active' 
                              LIMIT 5";
            $suggestion_stmt = $pdo->prepare($suggestion_sql);
            $suggestion_stmt->execute(["%{$query}%"]);
            $suggestions = $suggestion_stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'products' => $formatted_products,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_items' => (int)$total,
                    'items_per_page' => $limit
                ],
                'filters' => [
                    'query' => $query,
                    'category' => $category,
                    'min_price' => $min_price,
                    'max_price' => $max_price,
                    'sort' => $sort,
                    'order' => $order
                ],
                'suggestions' => $suggestions
            ]
        ]);
        
    } else {
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
