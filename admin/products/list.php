<?php

/**
 * Liste des produits - Administration
 * ElvyMade - Site de prospection d'articles
 */

// Inclusion des fichiers de configuration
require_once '../../config/database.php';
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Vérifier si l'utilisateur est administrateur
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Connexion à la base de données
$db = getDBConnection();

// Paramètres de recherche et filtrage
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$items_per_page = 20;

// Construction de la requête
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(p.nom LIKE ? OR p.description LIKE ?)";
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($category_filter > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if ($status_filter !== '') {
    $where_conditions[] = "p.disponible = ?";
    $params[] = ($status_filter === 'available') ? 1 : 0;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Compter le total
$count_sql = "SELECT COUNT(*) FROM products p $where_clause";
$count_stmt = $db->prepare($count_sql);
$count_stmt->execute($params);
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $items_per_page);

// Récupérer les produits
$offset = ($page - 1) * $items_per_page;
$sql = "
    SELECT p.*, c.nom as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    $where_clause 
    ORDER BY p.created_at DESC 
    LIMIT $items_per_page OFFSET $offset
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Récupérer les catégories pour le filtre
$categories_stmt = $db->query("SELECT * FROM categories ORDER BY nom ASC");
$categories = $categories_stmt->fetchAll();

// Messages non lus pour le sidebar
$unread_messages = 0;
try {
    $stmt = $db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'");
    $unread_messages = $stmt->fetchColumn();
} catch (Exception $e) {
    // Ignorer l'erreur si la table n'existe pas
}

// Variables pour le layout
$page_title = 'Gestion des Produits';
$page_icon = 'fas fa-box';
$header_actions = '<a href="add.php" class="btn btn-admin-primary"><i class="fas fa-plus"></i> Ajouter un produit</a>';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Administration ElvyMade</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>
    /* ==============================================
   PRODUCT LIST STYLES - ADMINISTRATION
   ============================================== */

    /* En-tête de contenu */
    .content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .content-title h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--admin-dark);
        margin-bottom: 0.25rem;
    }

    .content-title p {
        color: var(--text-muted);
        font-size: 0.875rem;
    }

    /* Conteneur de filtres */
    .filters-container {
        background: white;
        padding: 1.5rem;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-sm);
        margin-bottom: 1.5rem;
    }

    .filters-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: flex-end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
    }

    .filter-group label {
        font-size: 0.8125rem;
        color: var(--text-muted);
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .filter-input {
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        font-size: 0.9375rem;
        transition: var(--transition);
        width: 100%;
    }

    .filter-input:focus {
        outline: none;
        border-color: var(--admin-primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .filter-select {
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        font-size: 0.9375rem;
        background-color: white;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364728B' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 16px 12px;
        appearance: none;
        transition: var(--transition);
        width: 100%;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--admin-primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    /* Tableau des produits */
    .admin-table-container {
        background: white;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 1.5rem;
        overflow-x: auto;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    .admin-table th {
        background: var(--gray-50);
        font-weight: 600;
        color: var(--admin-dark);
        font-size: 0.8125rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        text-align: left;
    }

    .admin-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--admin-dark);
        font-size: 0.9375rem;
        vertical-align: middle;
    }

    .admin-table tr:last-child td {
        border-bottom: none;
    }

    .admin-table tr:hover {
        background: rgba(79, 70, 229, 0.03);
    }

    /* Styles spécifiques pour les cellules */
    .product-thumb {
        width: 60px;
        height: 60px;
        border-radius: var(--border-radius);
        object-fit: cover;
        border: 1px solid var(--border-color);
        background-color: var(--gray-50);
    }

    .product-info {
        display: flex;
        flex-direction: column;
    }

    .product-info strong {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .product-info small {
        font-size: 0.8125rem;
        color: var(--text-muted);
        line-height: 1.4;
    }

    /* Badges */
    .badge {
        padding: 0.375rem 0.5rem;
        border-radius: var(--border-radius);
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
        text-align: center;
        min-width: 90px;
    }

    .badge-success {
        background: #dcfce7;
        color: #166534;
    }

    .badge-danger {
        background: #fecaca;
        color: #991b1b;
    }

    /* Boutons d'action */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .btn-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
    }

    .btn-admin-outline {
        background: transparent;
        border: 1px solid var(--border-color);
        color: var(--admin-dark);
    }

    .btn-admin-outline:hover {
        background: var(--gray-50);
        border-color: var(--admin-primary);
        color: var(--admin-primary);
    }

    .delete-btn {
        background: var(--admin-danger);
        color: white;
    }

    .delete-btn:hover {
        background: var(--admin-danger-dark);
        color: white;
    }

    /* État vide */
    .empty-state {
        padding: 2rem;
        text-align: center;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 2.5rem;
        color: var(--gray-400);
        margin-bottom: 1rem;
        display: inline-block;
    }

    .empty-state h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--admin-dark);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        font-size: 0.9375rem;
        max-width: 400px;
        margin: 0 auto;
    }

    /* Pagination */
    .admin-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1.5rem;
    }

    .admin-pagination a,
    .admin-pagination span {
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border-color);
        color: var(--gray-700);
        text-decoration: none;
        border-radius: var(--border-radius);
        transition: var(--transition);
        font-size: 0.875rem;
    }

    .admin-pagination a:hover {
        background: var(--gray-100);
        border-color: var(--admin-primary);
        color: var(--admin-primary);
    }

    .admin-pagination .active {
        background: var(--admin-primary);
        color: white;
        border-color: var(--admin-primary);
    }

    .admin-pagination .disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .filters-form {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .content-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .filters-form {
            grid-template-columns: 1fr;
        }

        .admin-table th,
        .admin-table td {
            padding: 0.75rem;
        }

        .action-buttons {
            flex-direction: column;
        }
    }

    @media (max-width: 480px) {
        .admin-table td {
            font-size: 0.875rem;
        }

        .product-thumb {
            width: 40px;
            height: 40px;
        }

        .badge {
            min-width: auto;
            padding: 0.25rem 0.375rem;
        }
    }

    /* Animation de chargement */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .admin-table-container,
    .filters-container,
    .admin-pagination {
        animation: fadeIn 0.4s ease-out;
    }

    /* Effet de confirmation de suppression */
    .delete-confirm {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        opacity: 0;
        visibility: hidden;
        transition: var(--transition);
    }

    .delete-confirm.active {
        opacity: 1;
        visibility: visible;
    }

    .delete-confirm-box {
        background: white;
        border-radius: var(--border-radius-lg);
        padding: 2rem;
        max-width: 500px;
        width: 90%;
        box-shadow: var(--shadow-lg);
        transform: translateY(20px);
        transition: var(--transition);
    }

    .delete-confirm.active .delete-confirm-box {
        transform: translateY(0);
    }

    .delete-confirm-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--admin-dark);
    }

    .delete-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1.5rem;
    }
</style>

<body>
    <div class="admin-layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="admin-main">
            <?php include '../includes/header.php'; ?>

            <div class="admin-content">
                <!-- Actions et filtres -->
                <div class="content-header">
                    <div class="content-title">
                        <h2>Liste des Produits</h2>
                        <p><?php echo $total_products; ?> produit<?php echo $total_products > 1 ? 's' : ''; ?> au total</p>

                    </div>
                     <?php if (!empty($header_actions)): ?>
                            <div class="header-actions">
                                <?php echo $header_actions; ?>
                            </div>
                        <?php endif; ?>
                </div>

                <!-- Filtres -->
                <div class="filters-container">
                    <form method="GET" action="list.php" class="filters-form">
                        <div class="filter-group">
                            <input type="text" name="search" placeholder="Rechercher un produit..."
                                value="<?php echo htmlspecialchars($search); ?>" class="filter-input">
                        </div>

                        <div class="filter-group">
                            <select name="category" class="filter-select">
                                <option value="">Toutes les catégories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"
                                        <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <select name="status" class="filter-select">
                                <option value="">Tous les statuts</option>
                                <option value="available" <?php echo $status_filter === 'available' ? 'selected' : ''; ?>>Disponible</option>
                                <option value="unavailable" <?php echo $status_filter === 'unavailable' ? 'selected' : ''; ?>>Indisponible</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-admin-primary">
                            <i class="fas fa-search"></i>
                            Filtrer
                        </button>

                        <a href="list.php" class="btn btn-admin-outline">
                            <i class="fas fa-times"></i>
                            Réinitialiser
                        </a>
                    </form>
                    
                </div>

                <!-- Tableau des produits -->
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Nom</th>
                                <th>Catégorie</th>
                                <th>Prix</th>
                                <th>Statut</th>
                                <th>Date de création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($products) > 0): ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <img src="../../uploads/products/<?php echo $product['image'] ?: 'placeholder.jpg'; ?>"
                                                alt="<?php echo htmlspecialchars($product['nom']); ?>"
                                                class="product-thumb">
                                        </td>
                                        <td>
                                            <div class="product-info">
                                                <strong><?php echo htmlspecialchars($product['nom']); ?></strong>
                                                <small><?php echo truncateText($product['description'], 50); ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td><?php echo formatPrice($product['prix']); ?></td>
                                        <td>
                                            <?php if ($product['disponible']): ?>
                                                <span class="badge badge-success">Disponible</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Indisponible</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatDateFrench($product['created_at'], 'd/m/Y H:i'); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="../../pages/product.php?id=<?php echo $product['id']; ?>"
                                                    class="btn btn-sm btn-admin-outline" title="Voir" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit.php?id=<?php echo $product['id']; ?>"
                                                    class="btn btn-sm btn-admin-primary" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete.php?id=<?php echo $product['id']; ?>"
                                                    class="btn btn-sm btn-admin-danger delete-btn"
                                                    title="Supprimer"
                                                    data-confirm="Êtes-vous sûr de vouloir supprimer ce produit ?">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="empty-state">
                                            <i class="fas fa-box-open"></i>
                                            <h3>Aucun produit trouvé</h3>
                                            <p>Aucun produit ne correspond à vos critères de recherche.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="admin-pagination">
                        <?php
                        $base_url = 'list.php';
                        $current_params = $_GET;
                        unset($current_params['page']);
                        $query_string = !empty($current_params) ? '&' . http_build_query($current_params) : '';

                        echo generatePagination($page, $total_pages, $base_url . '?' . ltrim($query_string, '&'));
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../../assets/js/admin.js"></script>
</body>

</html>