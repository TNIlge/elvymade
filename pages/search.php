<?php

/**
 * Page de recherche moderne
 * ElvyMade - Site de prospection de bijoux
 */

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';

// Connexion à la base de données
$db = getDBConnection();

// Paramètres de recherche
$search_query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$category_filter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$sort_by = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'recent';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Construction de la requête SQL
$where_conditions = [];
$params = [];

if (!empty($search_query)) {
    $where_conditions[] = "(p.nom LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

if (!empty($category_filter)) {
    $where_conditions[] = "c.nom = ?";
    $params[] = $category_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Définir l'ordre de tri
$order_clause = match ($sort_by) {
    'name' => 'ORDER BY p.nom ASC',
    'price_asc' => 'ORDER BY p.prix ASC',
    'price_desc' => 'ORDER BY p.prix DESC',
    default => 'ORDER BY p.created_at DESC'
};

// Requête pour compter le total
$count_sql = "SELECT COUNT(*) as total 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              $where_clause";
$count_stmt = $db->prepare($count_sql);
$count_stmt->execute($params);
$total_products = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Requête pour récupérer les produits
$sql = "SELECT p.*, c.nom as category_name, c.image as category_image
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $where_clause 
        $order_clause 
        LIMIT $per_page OFFSET $offset";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer toutes les catégories pour le filtre
$categories_stmt = $db->query("SELECT * FROM categories ORDER BY nom ASC");
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul de la pagination
$total_pages = ceil($total_products / $per_page);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo !empty($search_query) ? "Recherche : " . htmlspecialchars($search_query) : "Recherche de bijoux"; ?> - ElvyMade</title>
    <meta name="description" content="Recherchez parmi notre collection exclusive de bijoux artisanaux du Cameroun.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/modern-style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body<?php echo isLoggedIn() ? ' class="logged-in"' : ''; ?>>
    <?php include '../includes/header.php'; ?>

    <!-- Contenu principal -->
    <main class="container" style="padding: 2rem 20px 4rem;">
        <!-- En-tête de recherche -->
        <div class="search-header text-center mb-4 fade-in">
            <h1 style="color: var(--primary-color); margin-bottom: 1rem;">
                <?php if (!empty($search_query)): ?>
                    <i class="fas fa-search"></i>
                    Résultats pour "<?php echo htmlspecialchars($search_query); ?>"
                <?php else: ?>
                    <i class="fas fa-gem"></i>
                    Notre Collection de Bijoux
                <?php endif; ?>
            </h1>
            <p style="color: var(--text-light); font-size: 1.1rem;">
                <?php echo $total_products; ?> bijou<?php echo $total_products > 1 ? 'x' : ''; ?> trouvé<?php echo $total_products > 1 ? 's' : ''; ?>
            </p>
        </div>

        <!-- Barre de recherche et filtres -->
        <div class="card mb-4 slide-in">
            <div class="card-body">
                <form method="GET" action="search.php" class="search-form">
                    <div class="grid grid-4" style="gap: 1rem; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">
                                <i class="fas fa-search"></i>
                                Rechercher
                            </label>
                            <input type="text" name="q" class="form-input"
                                placeholder="Nom du bijou, description..."
                                value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">
                                <i class="fas fa-tags"></i>
                                Catégorie
                            </label>
                            <select name="category" class="form-input">
                                <option value="">Toutes les catégories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['nom']); ?>"
                                        <?php echo $category_filter === $category['nom'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">
                                <i class="fas fa-sort"></i>
                                Trier par
                            </label>
                            <select name="sort" class="form-input">
                                <option value="recent" <?php echo $sort_by === 'recent' ? 'selected' : ''; ?>>Plus récents</option>
                                <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Nom A-Z</option>
                                <option value="price_asc" <?php echo $sort_by === 'price_asc' ? 'selected' : ''; ?>>Prix croissant</option>
                                <option value="price_desc" <?php echo $sort_by === 'price_desc' ? 'selected' : ''; ?>>Prix décroissant</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i>
                            Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Résultats de recherche -->
        <div class="search-results">
            <?php if (empty($products)): ?>
                <div class="card text-center" style="padding: 4rem 2rem;">
                    <div style="font-size: 4rem; color: var(--text-light); margin-bottom: 2rem;">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 style="color: var(--text-dark); margin-bottom: 1rem;">Aucun bijou trouvé</h3>
                    <p style="color: var(--text-light); margin-bottom: 2rem;">
                        <?php if (!empty($search_query)): ?>
                            Aucun bijou ne correspond à votre recherche "<?php echo htmlspecialchars($search_query); ?>".
                        <?php else: ?>
                            Aucun bijou disponible pour le moment.
                        <?php endif; ?>
                    </p>
                    <div class="d-flex justify-center gap-2">
                        <a href="search.php" class="btn btn-primary">
                            <i class="fas fa-gem"></i>
                            Voir tous les bijoux
                        </a>
                        <a href="../index.php" class="btn btn-outline">
                            <i class="fas fa-home"></i>
                            Retour à l'accueil
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid grid-3" style="gap: 2rem;">
                    <?php foreach ($products as $index => $product): ?>
                        <div class="product-card fade-in" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                            <div class="product-image">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="../uploads/products/<?php echo htmlspecialchars(basename($product['image'])); ?>"
                                        alt="<?php echo htmlspecialchars($product['nom']); ?>"
                                        loading="lazy">
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--primary-light), var(--primary-color)); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                                        <i class="fas fa-gem"></i>
                                    </div>
                                <?php endif; ?>

                                <?php if (isLoggedIn()): ?>
                                    <div class="favorite-btn <?php echo isProductInFavorites($_SESSION['user_id'], $product['id']) ? 'active' : ''; ?>"
                                 title="<?php echo isProductInFavorites($_SESSION['user_id'], $product['id']) ? 'Dans vos favoris' : 'Pas dans vos favoris'; ?>">
                                <i class="fas fa-heart"></i>
                            </div>
                                <?php endif; ?>
                            </div>

                            <div class="product-info">
                                <div class="product-category">
                                    <i class="fas fa-tag"></i>
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </div>
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
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-small">
                                    <i class="fas fa-eye"></i>
                                    Voir détails
                                </a>
                                <button onclick="openWhatsAppProduct('<?php echo addslashes($product['nom']); ?>', <?php echo $product['id']; ?>)" class="btn btn-success btn-small">
                                    <i class="fab fa-whatsapp"></i>
                                    WhatsApp
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
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
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                                    class="pagination-number <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>

                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
                                class="pagination-btn">
                                Suivant
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <div class="whatsapp-float">
        <a href="#" onclick="openWhatsAppGeneral()" class="whatsapp-btn" title="Contactez-nous sur WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>
 <script src="../assets/js/main.js"></script>
    <script src="../assets/js/whatsapp.js"></script>
    <script src="../assets/js/favorites.js"></script>
    <script>
        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in, .slide-in');
            elements.forEach((el, index) => {
                el.style.animationDelay = `${index * 0.1}s`;
            });
        });



        // Fonction WhatsApp générale
        function openWhatsAppGeneral() {
            const message = "Bonjour ! Je suis intéressé(e) par vos bijoux sur ElvyMade.";
            const whatsappUrl = `https://wa.me/237XXXXXXXXX?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }

        // Fonction WhatsApp pour un produit spécifique
        function openWhatsAppProduct(productName, productId) {
            const message = `Bonjour ! Je suis intéressé(e) par ce bijou :\n\n${productName}\nPouvez-vous me donner plus d'informations ?`;
            const whatsappUrl = `https://wa.me/237XXXXXXXXX?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
        
    </script>
    <?php include '../includes/footer.php'; ?>
    </body>

</html>

<style>

    /* Variables spécifiques à la page de recherche */
    :root {
        --search-primary-color: #8b5cf6;
        --search-primary-dark: #7c3aed;
        --search-primary-light: #c4b5fd;
        --search-success-color: #25d366;
        --search-success-dark: #128c7e;
        --search-danger-color: #ff4757;
        --search-text-dark: #1e293b;
        --search-text-light: #64748b;
        --search-border-color: #e2e8f0;
        --search-bg-light: #f8fafc;
        --search-border-radius: 8px;
        --search-border-radius-lg: 12px;
        --search-shadow-light: 0 1px 3px rgba(0, 0, 0, 0.1);
        --search-shadow-heavy: 0 4px 6px rgba(0, 0, 0, 0.1);
        --search-transition: all 0.2s ease;
    }

    /* ============ Filtres améliorés (scopé à la page) ============ */
    main.container .search-form {
        width: 100%;
        padding: 1.5rem;
        background-color: white;
        border-radius: var(--search-border-radius-lg);
        box-shadow: var(--search-shadow-light);
    }

    main.container .grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
    }

    main.container .form-group {
        margin-bottom: 0;
        position: relative;
    }

    main.container .form-label {
        display: block;
        margin-bottom: 0.75rem;
        font-size: 1rem;
        color: var(--search-text-dark);
        font-weight: 600;
    }

    main.container .form-label i {
        margin-right: 0.5rem;
        color: var(--search-primary-color);
        font-size: 0.9em;
    }

    main.container .form-input {
        width: 100%;
        padding: 0.85rem 1.25rem;
        border: 1px solid var(--search-border-color);
        border-radius: var(--search-border-radius);
        font-size: 1rem;
        transition: var(--search-transition);
        background-color: var(--search-bg-light);
        color: var(--search-text-dark);
        height: auto;
        min-height: 50px;
        box-sizing: border-box;
    }

    main.container .form-input:focus {
        outline: none;
        border-color: var(--search-primary-color);
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    }

    main.container select.form-input {
        padding-right: 3rem;
    }

    main.container .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.85rem 1.5rem;
        border-radius: var(--search-border-radius);
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: var(--search-transition);
        border: none;
        gap: 0.5rem;
        height: 50px;
    }

    /* ============ Blocs de produit (scopé à la page) ============ */
    main.container .grid-3 {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    main.container .product-card {
        background-color: white;
        border-radius: var(--search-border-radius-lg);
        overflow: hidden;
        box-shadow: var(--search-shadow-light);
        transition: var(--search-transition);
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
    }

    main.container .product-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--search-shadow-heavy);
    }

    main.container .product-image {
        position: relative;
        width: 100%;
        aspect-ratio: 1/1;
        overflow: hidden;
        background-color: #f5f3ff;
    }

    main.container .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    main.container .favorite-btn {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        color: var(--search-text-light);
        transition: var(--search-transition);
        box-shadow: var(--search-shadow-light);
        z-index: 2;
    }

    main.container .favorite-btn:hover,
    main.container .favorite-btn.active {
        color: var(--search-danger-color);
        transform: scale(1.1);
    }

    main.container .product-info {
        padding: 1.25rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    main.container .product-category {
        font-size: 0.75rem;
        color: var(--search-text-light);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    main.container .product-title {
        font-size: 1.1rem;
        margin: 0.25rem 0;
        color: var(--search-text-dark);
        line-height: 1.4;
        font-weight: 600;
    }

    main.container .product-description {
        color: var(--search-text-light);
        font-size: 0.85rem;
        margin: 0.5rem 0;
        line-height: 1.5;
        flex-grow: 1;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    main.container .product-price {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--search-primary-color);
        margin: 0.5rem 0 0;
    }

    main.container .product-actions {
        display: flex;
        gap: 0.75rem;
        padding: 0 1.25rem 1.25rem;
    }

    main.container .product-actions .btn {
        flex: 1;
        padding: 0.65rem 0.5rem;
        font-size: 0.85rem;
        z-index: 1;
        position: relative;
    }

    /* ============ Responsive (scopé à la page) ============ */
    
    @media (max-width: 1024px) {
        main.container .grid-4 {
            grid-template-columns: repeat(2, 1fr);
        }

        main.container .form-group {
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 768px) {
        main.container .grid-3 {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.25rem;
        }

        main.container .grid-4 {
            grid-template-columns: 1fr;
        }

        main.container .form-input,
        main.container .btn {
            min-height: 45px;

            margin-left: 20%;




        }

        main.container .product-actions {
            flex-direction: row;
        }

        main.container .product-actions .btn {
            font-size: 0.8rem;
            padding: 0.5rem;
        }
    }

    @media (max-width: 480px) {
        main.container .product-actions {
            flex-direction: column;
            gap: 0.5rem;
        }

        main.container .product-actions .btn {
            width: 100%;
        }
    }

    /* ============ Corrections d'interactivité ============ */
    main.container button,
    main.container a.btn {
        -webkit-tap-highlight-color: transparent;
        user-select: none;
    }

    main.container .product-card * {
        pointer-events: auto;
    }

    main.container .product-image {
        pointer-events: none;
    }

    main.container .product-image>* {
        pointer-events: auto;
    }

    /* ============ Animations (scopé à la page) ============ */
    main.container .fade-in {
        opacity: 0;
        animation: searchFadeIn 0.4s ease-out forwards;
    }

    main.container .slide-in {
        opacity: 0;
        transform: translateY(15px);
        animation: searchSlideIn 0.4s ease-out forwards;
    }

    @keyframes searchFadeIn {
        to {
            opacity: 1;
        }
    }

    @keyframes searchSlideIn {
        to {
            opacity: 1;
            transform: translateY(0);
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