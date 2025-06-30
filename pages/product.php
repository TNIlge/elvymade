<?php

/**
 * Page de détail d'un produit
 * ElvyMade - Site de prospection d'articles
 */

// Inclusion des fichiers de configuration
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';

// Vérifier si l'ID du produit est fourni et valide
if (empty($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: search.php');
    exit();
}

$product_id = (int)$_GET['id'];

// Connexion à la base de données
$db = getDBConnection();

// Récupérer les détails du produit
$stmt = $db->prepare("
    SELECT p.*, c.nom as category_name, c.image as category_image
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ? AND p.status = 'active'
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si le produit existe
if (!$product) {
    header('Location: search.php');
    exit();
}

// Récupérer les produits similaires (même catégorie)
$similar_stmt = $db->prepare("
    SELECT p.*, c.nom as category_name
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.category_id = ? AND p.id != ? AND p.status = 'active'
    ORDER BY RAND() 
    LIMIT 4
");
$similar_stmt->execute([$product['category_id'], $product_id]);
$similar_products = $similar_stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le produit est dans les favoris de l'utilisateur
$is_favorite = false;
if (isLoggedIn()) {
    $is_favorite = isProductInFavorites($_SESSION['user_id'], $product_id);
}

// Incrémenter le compteur de vues
try {
    $view_stmt = $db->prepare("UPDATE products SET views_count = views_count + 1 WHERE id = ?");
    $view_stmt->execute([$product_id]);
} catch (Exception $e) {
    // Ignorer les erreurs de compteur de vues
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['nom']); ?> - Elvy.Made</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($product['description'], 0, 160)); ?>">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<style>
    /* Styles dynamiques pour product.php */
    .product-container {
        padding: 2rem 0;
        animation: fadeIn 0.5s ease-out;
    }

    /* Fil d'Ariane amélioré */
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
        position: relative;
        padding: 0.2rem 0;
    }

    .breadcrumb a:hover {
        color: var(--primary-dark);
    }

    .breadcrumb a:hover::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background: var(--primary-color);
        transform-origin: left;
        animation: underlineGrow 0.3s ease-out forwards;
    }

    @keyframes underlineGrow {
        from {
            transform: scaleX(0);
        }

        to {
            transform: scaleX(1);
        }
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

    /* Grille produit responsive */
    .product-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2.5rem;
        margin-bottom: 3rem;
        align-items: start;
    }

    .product-gallery {
        position: relative;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        background: var(--gray-50);
        aspect-ratio: 1/1;
    }

    .product-main-image {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.5s ease;
    }

    .product-main-image img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: transform 0.5s ease;
    }

    .product-gallery:hover .product-main-image img {
        transform: scale(1.03);
    }

    .product-placeholder-large {
        text-align: center;
        color: var(--gray-400);
        padding: 2rem;
    }

    .product-placeholder-large i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    /* Informations produit */
    .product-info {
        padding: 1rem 0;
    }

    .product-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .product-category-badge a {
        display: inline-block;
        background: var(--primary-light);
        color: var(--primary-color);
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(233, 30, 99, 0.1);
    }

    .product-category-badge a:hover {
        background: var(--primary-color);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(233, 30, 99, 0.2);
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

    .favorite-btn-large i {
        font-size: 1.2rem;
    }

    .product-title {
        font-size: 2.2rem;
        color: var(--gray-900);
        margin-bottom: 1.5rem;
        line-height: 1.2;
        font-weight: 700;
    }

    .product-price-section {
        margin-bottom: 2rem;
        padding: 1rem 0;
        border-top: 1px dashed var(--gray-200);
        border-bottom: 1px dashed var(--gray-200);
    }

    .product-price-main {
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--primary-color);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .product-price-main::before {
        content: 'Prix:';
        font-size: 1rem;
        color: var(--gray-600);
        font-weight: 500;
    }

    .product-description {
        margin-bottom: 2rem;
    }

    .product-description h3 {
        font-size: 1.4rem;
        margin-bottom: 1rem;
        color: var(--gray-800);
        position: relative;
        padding-bottom: 0.5rem;
    }

    .product-description h3::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: var(--primary-color);
    }

    .product-description p {
        color: var(--gray-600);
        line-height: 1.7;
        font-size: 1.05rem;
    }

    /* Boutons d'action */
    .product-actions {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .btn-success {
        background: #25D366;
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 0.7rem;
        font-weight: 600;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3);
        text-decoration: none;
    }

    .btn-success:hover {
        background: #1DA851;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(37, 211, 102, 0.4);
    }

    .btn-outline {
        background: transparent;
        color: var(--primary-color);
        border: 2px solid var(--primary-color);
        padding: 1rem 2rem;
        border-radius: 0.7rem;
        font-weight: 600;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        text-decoration: none;
    }

    .btn-outline:hover {
        background: var(--primary-color);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(233, 30, 99, 0.3);
    }

    /* Métadonnées produit */
    .product-meta {
        display: flex;
        gap: 2rem;
        color: var(--gray-600);
        font-size: 0.9rem;
        flex-wrap: wrap;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .meta-item i {
        color: var(--primary-color);
        font-size: 1.1rem;
    }

    /* Onglets d'information */
    .product-additional-info {
        margin-bottom: 3rem;
    }

    .info-tabs {
        display: flex;
        border-bottom: 1px solid var(--gray-200);
        margin-bottom: 1.5rem;
        gap: 0.5rem;
    }

    .tab-btn {
        padding: 0.8rem 1.5rem;
        background: transparent;
        border: none;
        color: var(--gray-600);
        font-weight: 600;
        cursor: pointer;
        position: relative;
        transition: all 0.3s ease;
        border-radius: 0.5rem 0.5rem 0 0;
        font-size: 1rem;
    }

    .tab-btn:hover {
        color: var(--primary-color);
        background: rgba(233, 30, 99, 0.05);
    }

    .tab-btn.active {
        color: var(--primary-color);
        background: rgba(233, 30, 99, 0.1);
    }

    .tab-btn.active::after {
        content: "";
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        height: 3px;
        background: var(--primary-color);
        border-radius: 3px 3px 0 0;
    }

    .tab-pane {
        display: none;
        padding: 1.5rem 0;
        animation: fadeIn 0.4s ease-out;
    }

    .tab-pane.active {
        display: block;
    }

    /* Grille des détails */
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .detail-item {
        background: var(--gray-50);
        padding: 1rem;
        border-radius: 0.7rem;
        border-left: 3px solid var(--primary-color);
        transition: all 0.3s ease;
    }

    .detail-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .detail-item strong {
        display: block;
        color: var(--gray-600);
        font-size: 0.85rem;
        margin-bottom: 0.3rem;
    }

    .detail-item span {
        font-weight: 500;
        font-size: 1.05rem;
    }

    .status-available {
        color: #25D366;
        font-weight: 600;
    }

    .product-full-description {
        color: var(--gray-600);
        line-height: 1.7;
        font-size: 1.05rem;
    }

    /* Options de contact */
    .contact-options {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }

    /* Informations de livraison */
    .delivery-info {
        display: grid;
        gap: 1.5rem;
    }

    .delivery-item {
        display: flex;
        gap: 1.2rem;
        align-items: flex-start;
        padding: 1.2rem;
        background: var(--gray-50);
        border-radius: 0.7rem;
        transition: all 0.3s ease;
    }

    .delivery-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .delivery-item i {
        font-size: 1.5rem;
        color: var(--primary-color);
        margin-top: 0.2rem;
        flex-shrink: 0;
    }

    .delivery-item div {
        flex: 1;
    }

    .delivery-item strong {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--gray-800);
        font-size: 1.1rem;
    }

    .delivery-item p {
        color: var(--gray-600);
        line-height: 1.6;
        margin: 0;
    }

  /* Section Produits Similaires - Version améliorée */
.similar-products {
    margin-top: 4rem;
    padding: 2rem 0;
    border-top: 1px solid var(--gray-200);
}

.similar-products h2 {
    font-size: 1.8rem;
    margin-bottom: 2rem;
    color: var(--gray-900);
    position: relative;
    padding-bottom: 0.7rem;
}

.similar-products h2::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    width: 70px;
    height: 4px;
    background: var(--primary-color);
    border-radius: 2px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.5rem;
}

.product-card {
    background: white;
    border-radius: 0.8rem;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

/* Conteneur image redimensionné */
.product-image-container {
    position: relative;
    width: 100%;
    height: 120px; /* Hauteur fixe réduite */
    overflow: hidden;
    flex-shrink: 0;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-card:hover .product-image {
    transform: scale(1.05);
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

/* Contenu de la carte mieux structuré */
.product-card-content {
    padding: 1.2rem;
    flex: 1;
    display: flex;
    flex-direction: column;
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
    margin-bottom: 0.7rem;
    color: var(--gray-900);
    line-height: 1.4;
    flex: 1;
}

.product-title a {
    text-decoration: none;
    color: inherit;
    transition: color 0.3s ease;
    display: block;
}

.product-title a:hover {
    color: var(--primary-color);
}

.product-price {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 1.2rem;
}

/* Boutons d'action visibles */
.product-actions {
    display: flex;
    gap: 0.7rem;
    margin-top: auto;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 0.6rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    flex: 1;
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(233, 30, 99, 0.3);
}

.btn-success.btn-sm {
    background: #25D366;
    color: white;
    border: none;
    padding: 0.6rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    flex: 1;
}

.btn-success.btn-sm:hover {
    background: #1DA851;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(37, 211, 102, 0.3);
}

/* Bouton favori positionné correctement */
.favorite-btn {
    position: absolute;
    top: 0.7rem;
    right: 0.7rem;
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

/* Responsive */
@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
    
    .product-image-container {
        height: 150px;
    }
    
    .product-actions {
        flex-direction: column;
    }
    
    .btn-primary,
    .btn-success.btn-sm {
        width: 100%;
    }
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
        .product-details {
            grid-template-columns: 1fr;
        }

        .product-actions {
            flex-direction: column;
        }

        .btn-success,
        .btn-outline {
            width: 100%;
            justify-content: center;
        }

        .info-tabs {
            overflow-x: auto;
            padding-bottom: 0.5rem;
            scrollbar-width: thin;
        }

        .info-tabs::-webkit-scrollbar {
            height: 4px;
        }

        .info-tabs::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 2px;
        }

        .tab-btn {
            white-space: nowrap;
        }
    }

    /* Effet de chargement */
    .skeleton-loading {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
        border-radius: 0.5rem;
        color: transparent;
    }

    @keyframes shimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }
    /* Galerie produit principale */
.product-gallery {
    position: relative;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    background: var(--gray-50);
    aspect-ratio: 1/1;
    margin: 1rem;
    border: 1px solid var(--gray-200);
}

.product-main-image {
    width: calc(100% - 2rem);
    height: calc(100% - 2rem);
    margin: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.5s ease;
    background: white;
    border-radius: 0.5rem;
}

.product-main-image img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
    transition: transform 0.5s ease;
    padding: 1rem;
}

.product-gallery:hover .product-main-image img {
    transform: scale(1.03);
}

.product-placeholder-large {
    text-align: center;
    color: var(--gray-400);
    padding: 2rem;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.product-placeholder-large i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

</style>

<body<?php echo isLoggedIn() ? ' class="logged-in"' : ''; ?>>
    <?php include '../includes/header.php'; ?>
  
    <div class="container">
        <!-- Fil d'Ariane -->
        <nav class="breadcrumb">
            <a href="../index.php">Accueil</a>
            <span class="breadcrumb-separator">/</span>
            <a href="category.php?cat=<?php echo urlencode($product['category_name']); ?>">
                <?php echo htmlspecialchars($product['category_name']); ?>
            </a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current"><?php echo htmlspecialchars($product['nom']); ?></span>
        </nav>

        <div class="product-container">
            <!-- Détails du produit -->
            <div class="product-details">
                <div class="product-gallery">
                    <div class="product-main-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="../uploads/products/<?php echo htmlspecialchars($product['image']); ?>"
                                alt="<?php echo htmlspecialchars($product['nom']); ?>"
                                id="mainProductImage">
                        <?php else: ?>
                            <div class="product-placeholder-large">
                                <i class="fas fa-image"></i>
                                <p>Image non disponible</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="product-info">
                    <div class="product-header">
                        <div class="product-category-badge">
                            <a href="category.php?cat=<?php echo urlencode($product['category_name']); ?>">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </a>
                        </div>

                        <?php if (isLoggedIn() && !empty($product['id'])): ?>
                            <button class="favorite-btn-large <?php echo !empty($is_favorite) ? 'active' : ''; ?>"
                                onclick="toggleFavorite(<?php echo (int)$product['id']; ?>)"
                                data-product-id="<?php echo (int)$product['id']; ?>">
                                <i class="fas fa-heart"></i>
                                <span><?php echo !empty($is_favorite) ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?></span>
                            </button>
                        <?php endif; ?>
                    </div>

                    <h1 class="product-title"><?php echo htmlspecialchars($product['nom']); ?></h1>

                    <div class="product-price-section">
                        <div class="product-price-main">
                            <?php echo formatPrice($product['prix']); ?>
                        </div>
                    </div>

                    <div class="product-description">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>

                    <div class="product-actions">
                        <a href="#" onclick="contactWhatsApp(<?php echo $product['id']; ?>); return false;" class="btn btn-success btn-large">
                            <i class="fab fa-whatsapp"></i>
                            Contacter via WhatsApp
                        </a>

                        <button onclick="shareProduct()" class="btn btn-outline">
                            <i class="fas fa-share-alt"></i>
                            Partager
                        </button>
                    </div>

                    <div class="product-meta">
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Publié le <?php echo formatDateFrench($product['created_at'], 'd/m/Y'); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-eye"></i>
                            <span><?php echo $product['views_count']; ?> vues</span>
                        </div>
                        <?php if (!empty($product['ville'])): ?>
                            <div class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($product['ville']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Informations supplémentaires -->
            <div class="product-additional-info">
                <div class="info-tabs">
                    <button class="tab-btn active" onclick="showTab('details')">Détails</button>
                    <button class="tab-btn" onclick="showTab('contact')">Contact vendeur</button>
                    <button class="tab-btn" onclick="showTab('delivery')">Livraison</button>
                </div>

                <div class="tab-content">
                    <div id="details" class="tab-pane active">
                        <h3>Détails du produit</h3>
                        <div class="details-grid">
                            <div class="detail-item">
                                <strong>Catégorie :</strong>
                                <span><?php echo htmlspecialchars($product['category_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <strong>Prix :</strong>
                                <span><?php echo formatPrice($product['prix']); ?></span>
                            </div>
                            <div class="detail-item">
                                <strong>Disponibilité :</strong>
                                <span class="<?php echo $product['disponible'] ? 'status-available' : 'status-unavailable'; ?>">
                                    <?php echo $product['disponible'] ? 'Disponible' : 'Non disponible'; ?>
                                </span>
                            </div>
                            <?php if (!empty($product['ville'])): ?>
                                <div class="detail-item">
                                    <strong>Localisation :</strong>
                                    <span><?php echo htmlspecialchars($product['ville']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-full-description">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        </div>
                    </div>

                    <div id="contact" class="tab-pane">
                        <h3>Contacter le vendeur</h3>
                        <p>Pour plus d'informations sur ce produit ou pour passer commande, contactez-nous directement via WhatsApp.</p>
                        <div class="contact-options">
                            <a href="#" onclick="contactWhatsApp(<?php echo $product['id']; ?>); return false;" class="btn btn-success">
                                <i class="fab fa-whatsapp"></i>
                                WhatsApp
                            </a>
                            <a href="contact.php" class="btn btn-outline">
                                <i class="fas fa-envelope"></i>
                                Formulaire de contact
                            </a>
                        </div>
                    </div>

                    <div id="delivery" class="tab-pane">
                        <h3>Informations de livraison</h3>
                        <div class="delivery-info">
                            <div class="delivery-item">
                                <i class="fas fa-truck"></i>
                                <div>
                                    <strong>Livraison disponible</strong>
                                    <p>Nous livrons dans toutes les grandes villes du Cameroun</p>
                                </div>
                            </div>
                            <div class="delivery-item">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <strong>Délai de livraison</strong>
                                    <p>2-5 jours ouvrables selon votre localisation</p>
                                </div>
                            </div>
                            <div class="delivery-item">
                                <i class="fas fa-money-bill-wave"></i>
                                <div>
                                    <strong>Frais de livraison</strong>
                                    <p>À discuter avec le vendeur selon la destination</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produits similaires -->
            <?php if (!empty($similar_products)): ?>
                <div class="similar-products">
                    <h2>Produits similaires</h2>
                    <div class="products-grid">
                        <?php foreach ($similar_products as $similar): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <?php if (!empty($similar['image'])): ?>
                                        <img src="../uploads/products/<?php echo htmlspecialchars($similar['image']); ?>"
                                            alt="<?php echo htmlspecialchars($similar['nom']); ?>"
                                            loading="lazy">
                                    <?php else: ?>
                                        <div class="product-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (isLoggedIn()): ?>
                                        <button class="favorite-btn <?php echo isProductInFavorites($_SESSION['user_id'], $similar['id']) ? 'active' : ''; ?>"
                                            onclick="toggleFavorite(<?php echo $similar['id']; ?>)"
                                            data-product-id="<?php echo $similar['id']; ?>">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <div class="product-info">
                                    <div class="product-category">
                                        <?php echo htmlspecialchars($similar['category_name']); ?>
                                    </div>
                                    <h3 class="product-title">
                                        <a href="product.php?id=<?php echo $similar['id']; ?>">
                                            <?php echo htmlspecialchars($similar['nom']); ?>
                                        </a>
                                    </h3>
                                    <div class="product-price">
                                        <?php echo formatPrice($similar['prix']); ?>
                                    </div>
                                </div>

                                <div class="product-actions">
                                    <a href="product.php?id=<?php echo $similar['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                        Voir
                                    </a>
                                    <a href="#" onclick="contactWhatsApp(<?php echo $similar['id']; ?>); return false;" class="btn btn-success btn-sm">
                                        <i class="fab fa-whatsapp"></i>
                                        WhatsApp
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Bouton WhatsApp flottant -->
    <div class="whatsapp-float">
        <a href="#" onclick="contactWhatsApp(<?php echo $product['id']; ?>); return false;" class="whatsapp-btn">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/whatsapp.js"></script>
    <script src="../assets/js/favorites.js"></script>
    <script>
        function showTab(tabId) {
            // Masquer tous les onglets
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });

            // Désactiver tous les boutons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Afficher l'onglet sélectionné
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
        }

        function shareProduct() {
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo htmlspecialchars($product['nom']); ?>',
                    text: '<?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>',
                    url: window.location.href
                });
            } else {
                // Fallback pour les navigateurs qui ne supportent pas l'API Web Share
                const url = window.location.href;
                navigator.clipboard.writeText(url).then(() => {
                    alert('Lien copié dans le presse-papiers !');
                }).catch(() => {
                    // Fallback si le clipboard n'est pas disponible
                    const textArea = document.createElement('textarea');
                    textArea.value = url;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    alert('Lien copié dans le presse-papiers !');
                });
            }
        }

        function contactWhatsApp(productId) {
            const productName = '<?php echo addslashes($product['nom']); ?>';
            const productPrice = '<?php echo formatPrice($product['prix']); ?>';
            const productUrl = window.location.href;

            const message = `Bonjour ! Je suis intéressé(e) par ce produit :\n\n` +
                `📦 ${productName}\n` +
                `💰 ${productPrice}\n` +
                `🔗 ${productUrl}\n\n` +
                `Pouvez-vous me donner plus d'informations ?`;

            const whatsappUrl = `https://wa.me/237658470529?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
    </script>
    </body>

</html>