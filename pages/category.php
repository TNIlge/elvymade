<?php

/**
 * Page des catégories
 * ElvyMade - Site de prospection d'articles
 */

// Inclusion des fichiers de configuration
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';



// Connexion à la base de données
$db = getDBConnection();

// Paramètres de filtrage
$category_filter = isset($_GET['cat']) ? sanitizeInput($_GET['cat']) : '';
$sort_by = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'recent';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Récupérer toutes les catégories
$categories_stmt = $db->query("SELECT * FROM categories ORDER BY nom ASC");
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Si une catégorie spécifique est sélectionnée
$selected_category = null;
$products = [];
$total_products = 0;

if (!empty($category_filter)) {
    // Récupérer les détails de la catégorie sélectionnée
    $cat_stmt = $db->prepare("SELECT * FROM categories WHERE nom = ?");
    $cat_stmt->execute([$category_filter]);
    $selected_category = $cat_stmt->fetch(PDO::FETCH_ASSOC);

    if ($selected_category) {
        // Définir l'ordre de tri
        $order_clause = match ($sort_by) {
            'name' => 'ORDER BY p.nom ASC',
            'price_asc' => 'ORDER BY p.prix ASC',
            'price_desc' => 'ORDER BY p.prix DESC',
            default => 'ORDER BY p.created_at DESC'
        };

        // Compter le total de produits dans cette catégorie
        $count_stmt = $db->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = ?");
        $count_stmt->execute([$selected_category['id']]);
        $total_products = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Récupérer les produits de la catégorie
        $products_stmt = $db->prepare("
            SELECT p.*, c.nom as category_name
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id = ? 
            $order_clause 
            LIMIT $per_page OFFSET $offset
        ");
        $products_stmt->execute([$selected_category['id']]);
        $products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Calcul de la pagination
$total_pages = ceil($total_products / $per_page);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $selected_category ? htmlspecialchars($selected_category['nom']) . ' - ' : ''; ?>Catégories - ElvyMade</title>
    <meta name="description" content="Découvrez nos catégories de produits sur ElvyMade. Trouvez facilement ce que vous cherchez.">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>../assets/css/modern-style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>../assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>../assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>
    /* Styles dynamiques pour category.php */
    .container {
        padding: 2rem 1rem;
        animation: fadeIn 0.5s ease-out;
    }

    /* Vue d'ensemble des catégories */
    .categories-overview {
        margin-top: 1.5rem;
    }

    .categories-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .categories-header h1 {
        font-size: 2.5rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
        font-weight: 700;
    }

    .categories-header p {
        font-size: 1.1rem;
        color: var(--gray-600);
        max-width: 700px;
        margin: 0 auto;
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 2rem;
    }

    .category-card {
        background: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .category-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
    }

    .category-image {
        height: 200px;
        position: relative;
        overflow: hidden;
    }

    .category-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .category-card:hover .category-image img {
        transform: scale(1.1);
    }

    .category-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gray-100);
        color: var(--gray-400);
        font-size: 2.5rem;
    }

    .category-info {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .category-info h3 {
        font-size: 1.4rem;
        color: var(--gray-900);
        margin-bottom: 0.8rem;
        font-weight: 600;
    }

    .category-info p {
        color: var(--gray-600);
        margin-bottom: 1.5rem;
        flex: 1;
        line-height: 1.6;
    }

    .category-stats {
        margin-top: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .product-count {
        background: var(--primary-light);
        color: var(--primary-color);
        padding: 0.3rem 0.8rem;
        border-radius: 2rem;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .btn-primary {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 0.7rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        margin-top: 1rem;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(233, 30, 99, 0.3);
    }

    /* Vue détaillée d'une catégorie */
    .category-detail {
        margin-top: 1.5rem;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 2rem;
        font-size: 0.9rem;
        color: var(--gray-600);
    }

    .breadcrumb a {
        color: var(--primary-color);
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .breadcrumb a:hover {
        color: var(--primary-dark);
    }

    .breadcrumb-separator {
        color: var(--gray-400);
        display: flex;
        align-items: center;
    }

    .breadcrumb-current {
        color: var(--gray-800);
        font-weight: 600;
    }


    /* Contrôles de catégorie */
    .category-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .btn-outline {
        background: transparent;
        color: var(--primary-color);
        border: 2px solid var(--primary-color);
        padding: 0.7rem 1.5rem;
        border-radius: 0.7rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn-outline:hover {
        background: var(--primary-color);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(233, 30, 99, 0.2);
    }

    .sort-form {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .sort-select {
        padding: 0.7rem 1.5rem;
        border-radius: 0.7rem;
        border: 2px solid var(--gray-200);
        background: white;
        font-size: 1rem;
        color: var(--gray-700);
        cursor: pointer;
        transition: all 0.3s ease;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1rem;
        padding-right: 3rem;
    }

    .sort-select:hover {
        border-color: var(--primary-color);
    }

    .sort-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.1);
    }

    /* Produits */
    .category-products {
        margin-top: 2rem;
    }

    .no-products {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .no-products-icon {
        font-size: 3rem;
        color: var(--primary-light);
        margin-bottom: 1.5rem;
    }

    .no-products h3 {
        font-size: 1.5rem;
        color: var(--gray-800);
        margin-bottom: 0.5rem;
    }

    .no-products p {
        color: var(--gray-600);
        margin-bottom: 2rem;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 2rem;
    }

    .product-card {
        background: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
    }

    .product-image {
        height: 200px;
        position: relative;
        overflow: hidden;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .product-card:hover .product-image img {
        transform: scale(1.1);
    }

    .product-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gray-100);
        color: var(--gray-400);
        font-size: 2rem;
    }

    .favorite-btn {
        position: absolute;
        top: 0.8rem;
        right: 0.8rem;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: white;
        border: none;
        color: var(--gray-600);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        z-index: 2;
    }

    .favorite-btn:hover,
    .favorite-btn.active {
        background: var(--primary-color);
        color: white;
        transform: scale(1.1);
    }

    .product-info {
        padding: 1.5rem;
        flex: 1;
    }

    .product-category {
        color: var(--primary-color);
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .product-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--gray-900);
        line-height: 1.4;
    }

    .product-title a {
        text-decoration: none;
        color: inherit;
        transition: color 0.3s ease;
    }

    .product-title a:hover {
        color: var(--primary-color);
    }

    .product-description {
        color: var(--gray-600);
        font-size: 0.9rem;
        margin-bottom: 1rem;
        line-height: 1.5;
    }

    .product-price {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
    }

    .product-actions {
        display: flex;
        gap: 0.7rem;
        margin-top: auto;
    }

    .btn-primary.btn-sm {
        padding: 0.6rem 1.2rem;
        font-size: 0.9rem;
        flex: 1;
    }

    .btn-success.btn-sm {

        background: #25D366;
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 0.7rem;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        flex: 1;
    }

    .btn-success.btn-sm:hover {
        background: #1DA851;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(37, 211, 102, 0.3);
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: 3rem;
        flex-wrap: wrap;
    }

    .pagination-btn {
        background: white;
        color: var(--gray-700);
        border: 1px solid var(--gray-200);
        padding: 0.7rem 1.2rem;
        border-radius: 0.7rem;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .pagination-btn:hover {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        transform: translateY(-2px);
    }

    .pagination-numbers {
        display: flex;
        gap: 0.5rem;
    }

    .pagination-number {
        background: white;
        color: var(--gray-700);
        border: 1px solid var(--gray-200);
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.7rem;
        text-decoration: none;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .pagination-number:hover {
        background: var(--gray-100);
    }

    .pagination-number.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        font-weight: 600;
    }

    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .category-header-content {
            grid-template-columns: 1fr;
        }

        .category-header-image {
            height: 200px;
        }

        .category-header-info {
            padding: 1.5rem;
        }

        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }

        .category-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .sort-form {
            width: 100%;
        }

        .sort-select {
            width: 100%;
        }        @media (max-width: 768px) {
            .categories-grid,
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                justify-items: center;
            }
            .category-card,
            .product-card {
                width:  320px !important;
                min-width: 320px !important;
                max-width: 320px !important;
                margin-left: auto;
                margin-right: auto;
            }
        }
        
        @media (max-width: 400px) {
            .categories-grid,
            .products-grid {
                grid-template-columns: 1fr;
                justify-items: center;
            }
            .category-card,
            .product-card {
                width: 95vw !important;
                min-width: 0 !important;
                max-width: 95vw !important;
                margin-left: auto;
                margin-right: auto;
            }
        }
    }

    @media (max-width: 400px) {
        .categories-grid {
            grid-template-columns: 1fr;
        }

        .products-grid {
            grid-template-columns: 1fr;
        }

        .pagination {
            flex-direction: column;
            align-items: stretch;
        }

        .pagination-numbers {
            justify-content: center;
        }
    }
    
</style>
<body<?php echo isLoggedIn() ? ' class="logged-in"' : ''; ?>>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <?php if (!$selected_category): ?>
            <!-- Vue d'ensemble des catégories -->
            <div class="categories-overview">
                <div class="categories-header">
                    <h1>Nos Catégories</h1>
                    <p>Explorez notre large gamme de produits organisés par catégories</p>
                </div>

                <div class="categories-grid">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-card">
                            <div class="category-image">
                                <?php if (!empty($category['image'])): ?>
                                    <img src="../uploads/categories/<?php echo htmlspecialchars($category['image']); ?>"
                                        alt="<?php echo htmlspecialchars($category['nom']); ?>"
                                        loading="lazy">
                                <?php else: ?>
                                    <div class="category-placeholder">
                                        <i class="fas fa-th-large"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="category-info">
                                <h3><?php echo htmlspecialchars($category['nom']); ?></h3>
                                <p><?php echo htmlspecialchars($category['description']); ?></p>

                                <?php
                                // Compter les produits dans cette catégorie
                                $count_stmt = $db->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = ?");
                                $count_stmt->execute([$category['id']]);
                                $product_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
                                ?>

                                <div class="category-stats">
                                    <span class="product-count">
                                        <?php echo $product_count; ?> produit<?php echo $product_count > 1 ? 's' : ''; ?>
                                    </span>
                                </div>

                                <a href="category.php?cat=<?php echo urlencode($category['nom']); ?>"
                                    class="btn btn-primary">
                                    <i class="fas fa-arrow-right"></i>
                                    Voir les produits
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Vue détaillée d'une catégorie -->
            <div class="category-detail">
                <!-- Fil d'Ariane -->
                <nav class="breadcrumb">
                    <a href="../index.php">Accueil</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="category.php">Catégories</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-current"><?php echo htmlspecialchars($selected_category['nom']); ?></span>
                </nav>

                <!-- En-tête de la catégorie -->
                <div class="category-header">
                    <div class="category-header-content">
                        <div class="category-header-image">
                            <?php if (!empty($selected_category['image'])): ?>
                                <img src="../uploads/categories/<?php echo htmlspecialchars($selected_category['image']); ?>"
                                    alt="<?php echo htmlspecialchars($selected_category['nom']); ?>">
                            <?php else: ?>
                                <div class="category-placeholder-large">
                                    <i class="fas fa-th-large"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="category-header-info">
                            <h1><?php echo htmlspecialchars($selected_category['nom']); ?></h1>
                            <p><?php echo htmlspecialchars($selected_category['description']); ?></p>
                            <div class="category-stats">
                                <span class="stat-item">
                                    <i class="fas fa-box"></i>
                                    <?php echo $total_products; ?> produit<?php echo $total_products > 1 ? 's' : ''; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contrôles de tri et filtres -->
                <div class="category-controls">
                    <div class="category-navigation">
                        <a href="category.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i>
                            Toutes les catégories
                        </a>
                    </div>

                    <div class="sort-controls">
                        <form method="GET" action="category.php" class="sort-form">
                            <input type="hidden" name="cat" value="<?php echo htmlspecialchars($category_filter); ?>">
                            <select name="sort" class="sort-select" onchange="this.form.submit()">
                                <option value="recent" <?php echo $sort_by === 'recent' ? 'selected' : ''; ?>>Plus récents</option>
                                <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Nom A-Z</option>
                                <option value="price_asc" <?php echo $sort_by === 'price_asc' ? 'selected' : ''; ?>>Prix croissant</option>
                                <option value="price_desc" <?php echo $sort_by === 'price_desc' ? 'selected' : ''; ?>>Prix décroissant</option>
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Produits de la catégorie -->
                <div class="category-products">
                    <?php if (empty($products)): ?>
                        <div class="no-products">
                            <div class="no-products-icon">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <h3>Aucun produit dans cette catégorie</h3>
                            <p>Cette catégorie ne contient pas encore de produits.</p>
                            <a href="category.php" class="btn btn-primary">
                                <i class="fas fa-th-large"></i>
                                Voir toutes les catégories
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="products-grid">
                            <?php foreach ($products as $product): ?>
                                <div class="product-card">
                                    <div class="product-image">
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="../uploads/products/<?php echo htmlspecialchars($product['image']); ?>"
                                                alt="<?php echo htmlspecialchars($product['nom']); ?>"
                                                loading="lazy">
                                        <?php else: ?>
                                            <div class="product-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (isLoggedIn()): ?>
                                            <button class="favorite-btn <?php echo isProductInFavorites($_SESSION['user_id'], $product['id']) ? 'active' : ''; ?>"
                                                onclick="toggleFavorite(<?php echo $product['id']; ?>, this)"
                                                data-product-id="<?php echo $product['id']; ?>"
                                                title="<?php echo isProductInFavorites($_SESSION['user_id'], $product['id']) ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <div class="product-info">
                                        <h3 class="product-title">
                                            <a href="product.php?id=<?php echo $product['id']; ?>">
                                                <?php echo htmlspecialchars($product['nom']); ?>
                                            </a>
                                        </h3>
                                        <p class="product-description">
                                            <?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...
                                        </p>
                                        <div class="product-price">
                                            <?php echo number_format($product['prix'], 0, ',', ' '); ?> FCFA
                                        </div>
                                    </div>

                                    <div class="product-actions">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                            Voir détails
                                        </a>
                                        <a onclick="contactWhatsApp(<?php echo $product['id']; ?>)" class="btn btn-success btn-sm">
                                            <i class="fab fa-whatsapp"></i>
                                            WhatsApp
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <style>
                            .btn-primary.btn-sm,
                            .btn-success.btn-sm {
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                width: 100%;
                                min-width: 120px;
                                /* Ajustez selon vos besoins */
                                padding: 0.6rem 1.2rem;
                                font-size: 0.95rem;
                                border-radius: 0.7rem;
                                font-weight: 600;
                                box-sizing: border-box;
                                text-align: center;
                                gap: 0.5rem;
                            }

                            .product-actions {
                                display: flex;
                                gap: 0.7rem;
                            }

                            @media (max-width: 480px) {
                                .product-actions {
                                    flex-direction: column;
                                }

                                .btn-primary.btn-sm,
                                .btn-success.btn-sm {
                                    width: 100%;
                                }
                            }

                            /* Style cohérent pour tous les boutons favoris */
                            .favorite-btn {
                                position: absolute;
                                top: 0.8rem;
                                right: 0.8rem;
                                width: 36px;
                                height: 36px;
                                border-radius: 50%;
                                background: white;
                                border: none;
                                color: var(--gray-600);
                                cursor: pointer;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                                transition: all 0.3s ease;
                                z-index: 2;
                            }

                            .favorite-btn:hover,
                            .favorite-btn.active {
                                background: var(--primary-color);
                                color: white;
                                transform: scale(1.1);
                            }

                            .favorite-btn-large {
                                display: flex;
                                align-items: center;
                                gap: 0.5rem;
                                background: var(--gray-100);
                                border: none;
                                padding: 0.5rem 1.2rem;
                                border-radius: 2rem;
                                color: var(--gray-700);
                                cursor: pointer;
                                transition: all 0.3s ease;
                                font-weight: 500;
                                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
                            }

                            .favorite-btn-large:hover {
                                background: var(--gray-200);
                            }

                            .favorite-btn-large.active,
                            .favorite-btn-large.active:hover {
                                background: var(--primary-color);
                                color: white;
                                box-shadow: 0 4px 10px rgba(233, 30, 99, 0.3);
                            }
                        </style>
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?cat=<?php echo urlencode($category_filter); ?>&sort=<?php echo $sort_by; ?>&page=<?php echo $page - 1; ?>"
                                        class="pagination-btn">
                                        <i class="fas fa-chevron-left"></i>
                                        Précédent
                                    </a>
                                <?php endif; ?>

                                <div class="pagination-numbers">
                                    <?php
                                    $start = max(1, $page - 2);
                                    $end = min($total_pages, $page + 2);

                                    for ($i = $start; $i <= $end; $i++):
                                    ?>
                                        <a href="?cat=<?php echo urlencode($category_filter); ?>&sort=<?php echo $sort_by; ?>&page=<?php echo $i; ?>"
                                            class="pagination-number <?php echo $i === $page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                </div>

                                <?php if ($page < $total_pages): ?>
                                    <a href="?cat=<?php echo urlencode($category_filter); ?>&sort=<?php echo $sort_by; ?>&page=<?php echo $page + 1; ?>"
                                        class="pagination-btn">
                                        Suivant
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Bouton WhatsApp flottant -->
    <div class="whatsapp-float">
        <a href="#" onclick="openWhatsAppGeneral()" class="whatsapp-btn">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/whatsapp.js"></script>
      <script src="../assets/js/favorites.js"></script>
    </body>
       <script>
        function toggleFavorite(productId, buttonElement = null) {
    // Trouver le bouton concerné
    const favoriteBtn = buttonElement || document.querySelector(
        `.favorite-btn[data-product-id="${productId}"], .favorite-btn-large[data-product-id="${productId}"]`
    );

    if (!favoriteBtn) {
        console.error("Bouton favori non trouvé pour le produit", productId);
        return;
    }

    const isFavorite = favoriteBtn.classList.contains("active");
    const method = isFavorite ? "DELETE" : "POST";

    // URL de l'API - s'adapte selon l'emplacement de la page
    const isInPagesFolder = window.location.pathname.includes("/pages/");
    const baseUrl = isInPagesFolder ? "../api/favorites.php" : "api/favorites.php";
    const url = isFavorite ? `${baseUrl}?product_id=${productId}` : baseUrl;

    // Désactiver le bouton pendant la requête
    favoriteBtn.disabled = true;
    favoriteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    const requestOptions = {
        method: method,
        headers: {
            "Content-Type": "application/json",
        },
    };

    if (!isFavorite) {
        requestOptions.body = JSON.stringify({
            product_id: productId,
        });
    }

    fetch(url, requestOptions)
        .then((response) => {
            if (!response.ok) {
                return response.json().then((err) => Promise.reject(err));
            }
            return response.json();
        })
        .then((data) => {
            if (data.success) {
                // Mettre à jour l'état visuel du bouton
                favoriteBtn.classList.toggle("active");
                favoriteBtn.title = favoriteBtn.classList.contains("active") 
                    ? "Retirer des favoris" 
                    : "Ajouter aux favoris";
                favoriteBtn.innerHTML = '<i class="fas fa-heart"></i>';

                // Afficher une notification
                const message = isFavorite 
                    ? "Produit retiré des favoris" 
                    : "Produit ajouté aux favoris";
                showNotification(message, "success");
            } else {
                throw new Error(data.message || "Erreur inconnue");
            }
        })
        .catch((error) => {
            console.error("Erreur lors de la gestion du favori:", error);
            
            // Réinitialiser le bouton en cas d'erreur
            favoriteBtn.innerHTML = '<i class="fas fa-heart"></i>';
            
            if (error.message && error.message.includes("connecté")) {
                showNotification("Vous devez être connecté pour gérer vos favoris", "error");
                // Rediriger vers la page de connexion après un délai
                setTimeout(() => {
                    const loginUrl = isInPagesFolder ? "login.php" : "pages/login.php";
                    window.location.href = loginUrl + "?redirect=" + encodeURIComponent(window.location.pathname);
                }, 2000);
            } else {
                showNotification(error.message || "Erreur lors de la gestion du favori", "error");
            }
        })
        .finally(() => {
            // Réactiver le bouton
            favoriteBtn.disabled = false;
        });
}
       </script>
</html>
<style>
    /* Variables CSS */
    :root {
        --primary-color: #8b5cf6;
        --primary-dark: #7c3aed;
        --primary-light: #ddd6fe;
        --gray-900: #1e293b;
        --gray-800: #334155;
        --gray-700: #475569;
        --gray-600: #64748b;
        --gray-500: #94a3b8;
        --gray-400: #cbd5e1;
        --gray-300: #e2e8f0;
        --gray-200: #f1f5f9;
        --gray-100: #f8fafc;
        --white: #ffffff;
        --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
        --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.1);
        --radius-sm: 0.25rem;
        --radius-md: 0.5rem;
        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
        --transition: all 0.2s ease;
    }

    /* Styles de base */
    .category-page {
        padding: 2rem 1rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* En-tête */
    .category-header {
        text-align: center;
        margin-bottom: 3rem;
        padding: 2rem 0;
        background: linear-gradient(135deg, var(--primary-light), var(--white));
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-sm);
    }

    .category-header h1 {
        font-size: 2.5rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
        font-weight: 700;
    }

    .category-header p {
        font-size: 1.1rem;
        color: var(--gray-600);
        max-width: 700px;
        margin: 0 auto;
    }

    /* Fil d'Ariane */
    .breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 2rem;
        font-size: 0.9rem;
        color: var(--gray-600);
    }

    .breadcrumb a {
        color: var(--primary-color);
        text-decoration: none;
        transition: var(--transition);
        font-weight: 500;
    }

    .breadcrumb a:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    .breadcrumb-separator {
        color: var(--gray-400);
        display: flex;
        align-items: center;
    }

    .breadcrumb-current {
        color: var(--gray-800);
        font-weight: 600;
    }

    /* Grille de catégories */
    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .category-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
    }

    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .category-image {
        height: 200px;
        position: relative;
        overflow: hidden;
    }

    .category-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .category-card:hover .category-image img {
        transform: scale(1.05);
    }

    .category-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gray-100);
        color: var(--gray-400);
        font-size: 2.5rem;
    }

    .category-info {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .category-info h3 {
        font-size: 1.4rem;
        color: var(--gray-900);
        margin-bottom: 0.8rem;
        font-weight: 600;
    }

    .category-info p {
        color: var(--gray-600);
        margin-bottom: 1.5rem;
        flex: 1;
        line-height: 1.6;
    }

    .category-stats {
        margin-top: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .product-count {
        background: var(--primary-light);
        color: var(--primary-color);
        padding: 0.3rem 0.8rem;
        border-radius: 2rem;
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* Boutons */


    .btn-primary {
        background: var(--primary-color);
        color: var(--white);
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(139, 92, 246, 0.3);
    }

    .btn-outline {
        background: transparent;
        color: var(--primary-color);
        border: 2px solid var(--primary-color);
    }

    .btn-outline:hover {
        background: var(--primary-color);
        color: var(--white);
    }

    /* Contrôles */
    .category-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
        padding: 1rem;
        background: var(--gray-100);
        border-radius: var(--radius-md);
    }

    .sort-form {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .sort-select {
        padding: 0.7rem 1.5rem;
        border-radius: var(--radius-md);
        border: 2px solid var(--gray-300);
        background: var(--white);
        font-size: 1rem;
        color: var(--gray-700);
        cursor: pointer;
        transition: var(--transition);
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1rem;
        padding-right: 3rem;
    }

    .sort-select:hover {
        border-color: var(--primary-color);
    }

    .sort-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px var(--primary-light);
    }

    /* Produits */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .product-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        transition: var(--transition);
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .product-image {
        height: 200px;
        position: relative;
        overflow: hidden;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .product-card:hover .product-image img {
        transform: scale(1.05);
    }

    .favorite-btn {
        position: absolute;
        top: 0.8rem;
        right: 0.8rem;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--white);
        border: none;
        color: var(--gray-600);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        z-index: 2;
    }

    .favorite-btn:hover,
    .favorite-btn.active {
        background: var(--primary-color);
        color: var(--white);
        transform: scale(1.1);
    }

    .product-info {
        padding: 1.5rem;
        flex: 1;
    }

    .product-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--gray-900);
        line-height: 1.4;
    }

    .product-title a {
        text-decoration: none;
        color: inherit;
        transition: var(--transition);
    }

    .product-title a:hover {
        color: var(--primary-color);
    }

    .product-description {
        color: var(--gray-600);
        font-size: 0.9rem;
        margin-bottom: 1rem;
        line-height: 1.5;
    }

    .product-price {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
    }

    .product-actions {
        display: flex;
        gap: 0.7rem;
        margin-top: auto;
        padding: 0 1.5rem 1.5rem;
    }

    .btn-sm {
        padding: 0.6rem 1.2rem;
        font-size: 0.9rem;
        flex: 1;
    }

    .btn-success {
        background: #25D366;
        color: var(--white);
    }

    .btn-success:hover {
        background: #1DA851;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(37, 211, 102, 0.3);
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: 3rem;
        flex-wrap: wrap;
    }

    .pagination-btn {
        background: var(--white);
        color: var(--gray-700);
        border: 1px solid var(--gray-300);
        padding: 0.7rem 1.2rem;
        border-radius: var(--radius-md);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: var(--transition);
        font-weight: 500;
    }

    .pagination-btn:hover {
        background: var(--primary-color);
        color: var(--white);
        border-color: var(--primary-color);
        transform: translateY(-2px);
    }

    .pagination-numbers {
        display: flex;
        gap: 0.5rem;
    }

    .pagination-number {
        background: var(--white);
        color: var(--gray-700);
        border: 1px solid var(--gray-300);
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-md);
        text-decoration: none;
        transition: var(--transition);
        font-weight: 500;
    }

    .pagination-number:hover {
        background: var(--gray-100);
    }

    .pagination-number.active {
        background: var(--primary-color);
        color: var(--white);
        border-color: var(--primary-color);
        font-weight: 600;
    }

    /* Message vide */
    .no-products {
        text-align: center;
        padding: 4rem 2rem;
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        grid-column: 1 / -1;
    }

    .no-products-icon {
        font-size: 3rem;
        color: var(--primary-light);
        margin-bottom: 1.5rem;
    }

    .no-products h3 {
        font-size: 1.5rem;
        color: var(--gray-800);
        margin-bottom: 0.5rem;
    }

    .no-products p {
        color: var(--gray-600);
        margin-bottom: 2rem;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .category-header h1 {
            font-size: 2rem;
        }

        .categories-grid,
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        }

        .category-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .sort-form {
            width: 100%;
        }

        .sort-select {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .category-header h1 {
            font-size: 1.8rem;
        }

        .categories-grid,
        .products-grid {
            grid-template-columns: 1fr;
        }

        .product-actions {
            flex-direction: column;
        }

        .pagination {
            flex-direction: column;
            align-items: stretch;
        }

        .pagination-numbers {
            justify-content: center;
        }
    }

    :root {
        --primary: #8b5cf6;
        --primary-light: #e6f0ff;
        --secondary: #7c3aed;
        --dark: #1a1a2e;
        --light: #f8f9fa;
        --gray: #c4b5fd;
        --success: #a855f7;
        --warning: #f72585;
        --border-radius: 12px;
        --box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    /* Base Styles */
    .category-detail {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--dark);
        line-height: 1.6;
    }

    /* Breadcrumb */
    .breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 2.5rem;
        font-size: 0.9rem;
        color: var(--gray);
    }

    .breadcrumb a {
        color: var(--primary);
        text-decoration: none;
        transition: var(--transition);
        position: relative;
        padding-right: 1rem;
    }

    .breadcrumb a:after {
        content: '/';
        position: absolute;
        right: 0.25rem;
        color: var(--gray);
    }

    .breadcrumb a:hover {
        color: var(--secondary);
    }

    .breadcrumb-current {
        font-weight: 600;
        color: var(--dark);
    }

    /* Category Header */
    .category-header {
        background: white;
        border-radius: var(--border-radius);
        padding: 2.5rem;
        margin-bottom: 3rem;
        box-shadow: var(--box-shadow);
        display: flex;
        flex-direction: column;
        gap: 2rem;
        position: relative;
        overflow: hidden;
    }

    .category-header:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(to bottom, var(--primary), var(--success));
    }

    @media (min-width: 992px) {
        .category-header {
            flex-direction: row;
            align-items: center;
        }
    }

    .category-header-image {
        flex: 0 0 280px;
        height: 280px;
        border-radius: calc(var(--border-radius) - 4px);
        overflow: hidden;
        background: var(--light);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: var(--transition);
    }

    .category-header-image:hover {
        transform: rotate(-1deg) scale(1.02);
    }

    .category-header-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .category-placeholder-large {
        color: #d1d5db;
        font-size: 4rem;
        opacity: 0.5;
    }

    .category-header-info {
        flex: 1;
    }

    .category-header-info h1 {
        margin: 0 0 1rem;
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--dark);
        line-height: 1.2;
    }

    .category-header-info p {
        margin: 0 0 1.5rem;
        color: var(--gray);
        font-size: 1.05rem;
        max-width: 700px;
    }

    .category-stats {
        display: flex;
        gap: 1.5rem;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.95rem;
        color: var(--gray);
        background: var(--light);
        padding: 0.5rem 1rem;
        border-radius: 50px;
    }

    .stat-item i {
        color: var(--primary);
    }

    /* Category Controls */
    .category-controls {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    @media (min-width: 768px) {
        .category-controls {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }
    }

    .category-navigation .btn-outline {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border: 2px solid var(--primary);
        border-radius: 50px;
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition);
        background: white;
    }

    .category-navigation .btn-outline:hover {
        background: var(--primary-light);
        transform: translateX(-3px);
    }

    .sort-form {
        position: relative;
    }

    .sort-select {
        padding: 0.75rem 1.5rem 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 50px;
        background-color: white;
        color: var(--dark);
        font-size: 0.95rem;
        cursor: pointer;
        transition: var(--transition);
        appearance: none;
        min-width: 220px;
        font-weight: 500;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%234361ee' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 16px;
    }

    .sort-select:hover {
        border-color: var(--primary);
    }

    .sort-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }

    /* STYLE ULTRA-DYNAMIQUE - VERSION 4.0 */
    :root {
        --primary: #6C5CE7;
        --primary-hover: #5649C2;
        --secondary: #00CEFF;
        --whatsapp: #25D366;
        --whatsapp-hover: #128C7E;
        --dark: #2D3436;
        --light: #F5F6FA;
        --gray: #636E72;
        --accent: #FD79A8;
        --border-radius: 16px;
        --transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    /* CARTE PRODUIT REDESIGN */
    .product-card {
        position: relative;
        background: white;
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
        transition: var(--transition);
        transform-style: preserve-3d;
        will-change: transform;
        z-index: 1;
        margin-bottom: 25px;
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .product-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
    }

    /* IMAGE PRODUIT ANIMÉE */
    .product-image {
        position: relative;
        width: 100%;
        height: 220px;
        overflow: hidden;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .product-card:hover .product-image img {
        transform: scale(1.08);
    }

    /* CONTENU PRODUIT */
    .product-info {
        padding: 20px;
        position: relative;
        z-index: 2;
        background: white;
    }

    .product-title {
        margin: 0 0 12px;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark);
        line-height: 1.4;
    }

    .product-title a {
        color: inherit;
        text-decoration: none;
        position: relative;
        display: inline-block;
        transition: color 0.3s ease;
    }

    .product-title a:hover {
        color: var(--primary);
    }

    .product-description {
        color: var(--gray);
        font-size: 0.92rem;
        margin-bottom: 15px;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-price {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--primary);
        margin: 18px 0;
        display: flex;
        align-items: center;
    }

    /* DISPOSITION OPTIMISÉE DES BOUTONS */
    .product-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        padding: 0 20px 20px;
        position: relative;
        z-index: 3;
    }


    .btn i {
        font-size: 1.1rem;
    }

    /* BOUTON "VOIR DÉTAILS" */
    .btn-primary {
        background: var(--primary);
        color: white;
        box-shadow: 0 4px 12px rgba(108, 92, 231, 0.25);
    }

    .btn-primary:hover {
        background: var(--primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(108, 92, 231, 0.35);
    }

    /* BOUTON WHATSAPP */
    .btn-success {
        background: var(--whatsapp);
        color: white;
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25);
    }

    .btn-success:hover {
        background: var(--whatsapp-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(37, 211, 102, 0.35);
    }

    /* EFFET DE SURVOL DYNAMIQUE */
    .product-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(108, 92, 231, 0.03) 0%, rgba(0, 206, 255, 0.02) 100%);
        opacity: 0;
        transition: opacity 0.4s ease;
        z-index: 0;
    }

    .product-card:hover::before {
        opacity: 1;
    }

    /* ANIMATION D'APPARITION */
    @keyframes cardEntrance {
        from {
            opacity: 0;
            transform: translateY(20px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .product-card {
        animation: cardEntrance 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
    }

    /* DÉLAIS D'ANIMATION */
    .product-card:nth-child(1) {
        animation-delay: 0.1s;
    }

    .product-card:nth-child(2) {
        animation-delay: 0.2s;
    }

    .product-card:nth-child(3) {
        animation-delay: 0.3s;
    }

    .product-card:nth-child(4) {
        animation-delay: 0.4s;
    }

    .product-card:nth-child(5) {
        animation-delay: 0.5s;
    }

    .product-card:nth-child(6) {
        animation-delay: 0.6s;
    }

    /* BOUTON FAVORI */
    .favorite-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 38px;
        height: 38px;
        border: none;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(5px);
        color: var(--gray);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
        z-index: 10;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .favorite-btn:hover {
        color: var(--accent);
        transform: scale(1.15);
        background: rgba(255, 255, 255, 1);
    }

    .favorite-btn.active {
        color: var(--accent);
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .product-actions {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .btn {
            padding: 12px;
            font-size: 0.88rem;
        }

        .product-image {
            height: 200px;
        }
    }

    @media (max-width: 480px) {
        .product-info {
            padding: 16px;
        }

        .product-actions {
            padding: 0 16px 16px;
        }

        .product-title {
            font-size: 1.15rem;
        }
    }

    /* Micro-interactions */
    .favorite-btn {
        will-change: transform;
    }

    .btn {
        will-change: transform;
    }

    /* Loading state (optional) */
    @keyframes shimmer {
        0% {
            background-position: -468px 0
        }

        100% {
            background-position: 468px 0
        }
    }

    .loading-card {
        background: #f6f7f8;
        background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
        background-repeat: no-repeat;
        background-size: 800px 104px;
        animation: shimmer 1s linear infinite forwards;
        border-radius: var(--border-radius);
        height: 350px;
    }
</style>