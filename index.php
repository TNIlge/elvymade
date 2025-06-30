<?php
// Inclusion des fichiers de configuration
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/functions.php';

// Connexion à la base de données
$db = getDBConnection();

// Récupérer les catégories populaires
$stmt = $db->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id AND p.disponible = 1 
    GROUP BY c.id 
    ORDER BY product_count DESC 
    LIMIT 4
");
$popular_categories = $stmt->fetchAll();

// Récupérer les produits récents
$stmt = $db->query("
    SELECT p.*, c.nom as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.disponible = 1 
    ORDER BY p.created_at DESC 
    LIMIT 4
");
$recent_products = $stmt->fetchAll();

// Récupérer les produits populaires (avec le plus de vues)
$stmt = $db->query("
    SELECT p.*, c.nom as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.disponible = 1 
    ORDER BY RAND()
    LIMIT 4
");
$popular_products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elvy.Made - Bijoux de Luxe au Cameroun</title>
    <meta name="description" content="Elvy.Made - Découvrez notre collection exclusive de bijoux de luxe au Cameroun. Bagues, colliers, bracelets et plus encore.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body<?php echo isLoggedIn() ? ' class="logged-in"' : ''; ?>>

    <style>
        /* Styles pour la grille des catégories */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .category-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;

        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .category-link {
            text-decoration: none;
            color: inherit;
        }

        .category-image {
            position: relative;
            height: 200px;
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

        .category-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
            padding: 1.5rem;
            color: white;
        }

        .category-overlay h3 {
            margin: 0;
            font-size: 1.25rem;
        }

        .category-count {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .category-info {
            padding: 1.5rem;
        }

        .category-info h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.125rem;
            color: #333;
        }

        .category-info p {
            margin: 0 0 1rem 0;
            color: #666;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        /* Styles pour la grille des produits */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-card:hover {

            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-12px) scale(1.02);
        }

        .product-image-container {
            position: relative;
            height: 240px;
            overflow: hidden;
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

        .product-overlay {
            position: absolute;
            top: 0;
            right: 0;
            padding: 1rem;
        }

        .favorite-btn {
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
            color: #64748b;
            transition: var(--search-transition);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            z-index: 2;
        }

        .favorite-btn:hover {
            color: #ff4757;
            transform: scale(1.1);
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-category {
            font-size: 0.75rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .product-title {
            margin: 0 0 0.5rem 0;
            font-size: 1.125rem;
            color: #333;
        }

        .product-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .product-title a:hover {
            color: var(--primary-color);
        }

        .product-description {
            margin: 0 0 1rem 0;
            color: #666;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .product-price {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.125rem;
            margin-bottom: 1.25rem;
        }

        .product-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        /* Styles pour les en-têtes de section */
        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 2rem;
            margin: 0 0 0.5rem 0;
            color: #333;
        }

        .section-subtitle {
            font-size: 1rem;
            color: #666;
            margin: 0;
        }

        /* Styles responsives */
      @media (max-width: 768px) {
        .categories-grid, 
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

          .product-card {
            height: 530px; /* Hauteur fixe */
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

      
        .product-image-container {
            height: 265px !important; /* Hauteur fixe */
            flex-shrink: 0;
            overflow: hidden;
        }

       
        .product-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

 

        .product-actions {
            margin-top: auto;
            padding-top: 0.5rem;
        }
    }

    @media (max-width: 480px) {
    
        .products-grid {
            grid-template-columns: 1fr;
        }


    }
                /* Grille des catégories et produits avec blocs uniformes */
        .categories-grid
      {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
            justify-items: center;
        }
        
        /* Bloc catégorie */
        .category-card {
            width: 320px;
            height: 420px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
        }
        
     
        
        /* Image catégorie et produit */
        .category-image
      {
            width: 100%;
            height: 180px;
            overflow: hidden;
            flex-shrink: 0;
        }
        .category-image img
      {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Infos catégorie et produit */
        .category-info
       {
            flex: 1 1 auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
  
        /* Responsive */
        @media (max-width: 1024px) {
            .categories-grid
            {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            }
            .category-card
           {
                width: 260px;
                height: 400px;
            }
            .category-image
            {
                height: 140px;
            }
        }
        @media (max-width: 600px) {
            .categories-grid
           {
                grid-template-columns: 1fr;
                gap: 1.2rem;
            }
            .category-card
          {
                width: 100%;
                min-width: 0;
                height: 340px;
            }
            .category-image
            {
                height: 110px;
            }
        }
    </style>
   <?php include 'includes/header.php'; ?>

    <!-- Section Hero -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Découvrez l'Élégance des Bijoux Elvy.Made</h1>
                    <p>Collection exclusive de bijoux de luxe au Cameroun. Chaque pièce raconte une histoire unique d'artisanat et de beauté.</p>
                    <div class="hero-buttons">
                        <a href="pages/search.php" class="btn btn-primary">
                            <i class="fas fa-gem"></i>
                            Explorer la Collection
                        </a>
                        <a href="pages/category.php" class="btn btn-outline">
                            <i class="fas fa-th-large"></i>
                            Voir les Catégories
                        </a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="assets/images/hero.jpg?height=500&width=600" alt="Collection ElvyMade">
                </div>
            </div>
        </div>
    </section>



    <!-- Section Nouveautés -->
    <section class="section" style="background: var(--gray-50);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Nouveautés</h2>
                <p class="section-subtitle">Découvrez nos dernières créations bijoux</p>
            </div>

            <div class="products-grid">
                <?php foreach ($recent_products as $product): ?>
                    <div class="product-card fade-in-up">
                        <div class="product-image-container">
                            <img src="uploads/products/<?php echo $product['image'] ?: 'placeholder.jpg'; ?>"
                                alt="<?php echo htmlspecialchars($product['nom']); ?>"
                                class="product-image"
                                loading="lazy">

                            <div class="product-overlay">
<?php if (isLoggedIn()): ?>
   <button class="favorite-btn<?php echo isProductInFavorites($_SESSION['user_id'], $product['id']) ? ' active' : ''; ?>"
            onclick="toggleFavorite(<?php echo $product['id']; ?>, this)"
            data-product-id="<?php echo $product['id']; ?>"
            title="<?php echo isProductInFavorites($_SESSION['user_id'], $product['id']) ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>">
        <i class="fas fa-heart"></i>
    </button>
<?php endif; ?>
                            </div>
                        </div>

                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                            <h3 class="product-title">
                                <a href="pages/product.php?id=<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['nom']); ?>
                                </a>
                            </h3>
                            <p class="product-description">
                                <?php echo truncateText($product['description'], 80); ?>
                            </p>
                            <div class="product-price"><?php echo formatPrice($product['prix']); ?></div>

                            <div class="product-actions">
                                <a href="pages/product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                    Voir détails
                                </a>
                                <button onclick="openWhatsAppProduct('<?php echo htmlspecialchars($product['nom']); ?>', <?php echo $product['id']; ?>)"
                                    class="btn btn-secondary btn-sm">
                                    <i class="fab fa-whatsapp"></i>
                                    Contact
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center" style="margin-top: var(--spacing-8);">
                <a href="pages/search.php" class="btn btn-primary">
                    <i class="fas fa-gem"></i>
                    Voir tous les bijoux
                </a>
            </div>
        </div>
    </section>
    <!-- Section Catégories Populaires -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Nos Catégories</h2>
                <p class="section-subtitle">Explorez notre gamme complète de bijoux soigneusement sélectionnés</p>
            </div>

            <div class="categories-grid">
                <?php foreach ($popular_categories as $category): ?>
                    <div class="category-card fade-in-up">
                        <a href="pages/category.php?id=<?php echo $category['id']; ?>" class="category-link">
                            <div class="category-image">
                                <?php if (!empty($category['image'])): ?>
                                    <img src="uploads/categories/<?php echo htmlspecialchars($category['image']); ?>"
                                        alt="<?php echo htmlspecialchars($category['nom']); ?>"
                                        loading="lazy">
                                <?php else: ?>
                                    <img src="/placeholder.svg?height=200&width=300"
                                        alt="<?php echo htmlspecialchars($category['nom']); ?>">
                                <?php endif; ?>
                                <div class="category-overlay">
                                    <?php
                                    // Compter les produits dans cette catégorie
                                    $count_stmt = $db->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = ?");
                                    $count_stmt->execute([$category['id']]);
                                    $product_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
                                    ?>
                                    <h3><?php echo htmlspecialchars($category['nom']); ?></h3>
                                    <div class="category-count">
                                        <?php echo $product_count; ?> bijou<?php echo $product_count > 1 ? 'x' : ''; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="category-info">
                                <h3><?php echo htmlspecialchars($category['nom']); ?></h3>
                                <?php if ($category['description']): ?>
                                    <p><?php echo truncateText($category['description'], 80); ?></p>
                                <?php endif; ?>
                                <span class="category-count">
                                    <?php echo $category['product_count']; ?> produit<?php echo $category['product_count'] > 1 ? 's' : ''; ?>
                                </span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center" style="margin-top: var(--spacing-8);">
                <a href="pages/category.php" class="btn btn-secondary">
                    <i class="fas fa-th-large"></i>
                    Voir toutes les catégories
                </a>
            </div>
        </div>
    </section>

    <!-- Section Produits Populaires
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Coups de Cœur</h2>
                <p class="section-subtitle">Les bijoux préférés de nos clients</p>
            </div>
            
            <div class="products-grid">
                <?php foreach ($popular_products as $product): ?>
                    <div class="product-card fade-in-up">
                        <div class="product-image-container">
                            <img src="uploads/products/<?php echo $product['image'] ?: 'placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['nom']); ?>" 
                                 class="product-image"
                                 loading="lazy">
                            
                            <div class="product-overlay">
                                <?php if (isLoggedIn()): ?>
                                    <button onclick="toggleFavorite(<?php echo $product['id']; ?>, this)" 
                                            class="favorite-btn" 
                                            title="Ajouter aux favoris">
                                        <i class="far fa-heart"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                            <h3 class="product-title">
                                <a href="pages/product.php?id=<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['nom']); ?>
                                </a>
                            </h3>
                            <p class="product-description">
                                <?php echo truncateText($product['description'], 80); ?>
                            </p>
                            <div class="product-price"><?php echo formatPrice($product['prix']); ?></div>
                            
                            <div class="product-actions">
                                <a href="pages/product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                    Voir détails
                                </a>
                                <button onclick="openWhatsAppProduct('<?php echo htmlspecialchars($product['nom']); ?>', <?php echo $product['id']; ?>)" 
                                        class="btn btn-secondary btn-sm">
                                    <i class="fab fa-whatsapp"></i>
                                    Contact
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section> -->

    <!-- Section Services -->
    <section class="section" style="background: var(--gray-50);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Pourquoi Choisir ElvyMade ?</h2>
                <p class="section-subtitle">Notre engagement envers l'excellence</p>
            </div>

            <div class="services-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--spacing-6);">
                <div class="service-card" style="background: var(--white); padding: var(--spacing-6); border-radius: var(--border-radius-xl); text-align: center; box-shadow: var(--shadow);">
                    <div style="width: 60px; height: 60px; background: var(--primary-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-4);">
                        <i class="fas fa-gem" style="font-size: var(--font-size-xl); color: var(--primary-color);"></i>
                    </div>
                    <h3 style="margin-bottom: var(--spacing-3); color: var(--gray-900);">Qualité Premium</h3>
                    <p style="color: var(--gray-600);">Bijoux sélectionnés avec soin pour leur qualité exceptionnelle et leur design unique.</p>
                </div>

                <div class="service-card" style="background: var(--white); padding: var(--spacing-6); border-radius: var(--border-radius-xl); text-align: center; box-shadow: var(--shadow);">
                    <div style="width: 60px; height: 60px; background: var(--primary-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-4);">
                        <i class="fas fa-truck" style="font-size: var(--font-size-xl); color: var(--primary-color);"></i>
                    </div>
                    <h3 style="margin-bottom: var(--spacing-3); color: var(--gray-900);">Livraison Rapide</h3>
                    <p style="color: var(--gray-600);">Livraison gratuite à Douala et Yaoundé. Service express disponible.</p>
                </div>

                <div class="service-card" style="background: var(--white); padding: var(--spacing-6); border-radius: var(--border-radius-xl); text-align: center; box-shadow: var(--shadow);">
                    <div style="width: 60px; height: 60px; background: var(--primary-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-4);">
                        <i class="fas fa-headset" style="font-size: var(--font-size-xl); color: var(--primary-color);"></i>
                    </div>
                    <h3 style="margin-bottom: var(--spacing-3); color: var(--gray-900);">Support 24/7</h3>
                    <p style="color: var(--gray-600);">Notre équipe est disponible pour vous conseiller et vous accompagner.</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Bouton WhatsApp Fixe -->
    <div class="whatsapp-float">
        <a href="#" onclick="openWhatsAppGeneral()" class="whatsapp-btn" title="Contactez-nous sur WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>
  
    <script src="assets/js/main.js"></script>
    <script src="assets/js/whatsapp.js"></script>
  <script src="../assets/js/favorites.js"></script>
    <script>
        // Animation au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in-up').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
function toggleFavorite(productId, buttonElement = null) {
  // Trouver le bouton concerné
  const favoriteBtn =
    buttonElement ||
    document.querySelector(
      `.favorite-btn[data-product-id="${productId}"], .favorite-btn-large[data-product-id="${productId}"]`,
    )

  if (!favoriteBtn) {
    console.error("Bouton favori non trouvé pour le produit", productId)
    return
  }

  const isFavorite = favoriteBtn.classList.contains("active")
  const method = isFavorite ? "DELETE" : "POST"

  // URL de l'API - s'adapte selon l'emplacement de la page
  const isInPagesFolder = window.location.pathname.includes("/pages/")
  const baseUrl = isInPagesFolder ? "../api/favorites.php" : "api/favorites.php"
  const url = isFavorite ? `${baseUrl}?product_id=${productId}` : baseUrl

  // Désactiver le bouton pendant la requête
  favoriteBtn.disabled = true
  favoriteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'

  const requestOptions = {
    method: method,
    headers: {
      "Content-Type": "application/json",
    },
  }

  if (!isFavorite) {
    requestOptions.body = JSON.stringify({
      product_id: productId,
    })
  }

  fetch(url, requestOptions)
    .then((response) => {
      if (!response.ok) {
        return response.json().then((err) => Promise.reject(err))
      }
      return response.json()
    })
    .then((data) => {
      if (data.success) {
        // Mettre à jour l'état visuel du bouton
        favoriteBtn.classList.toggle("active")
        favoriteBtn.title = favoriteBtn.classList.contains("active") ? "Retirer des favoris" : "Ajouter aux favoris"
        favoriteBtn.innerHTML = '<i class="fas fa-heart"></i>'

        // Afficher une notification
        const message = isFavorite ? "Produit retiré des favoris" : "Produit ajouté aux favoris"
        showNotification(message, "success")
        // Mettre à jour le compteur de favoris
        updateFavoritesCount(isFavorite ? -1 : 1)
      } else {
        throw new Error(data.message || "Erreur inconnue")
      }
    })
    .catch((error) => {
      console.error("Erreur lors de la gestion du favori:", error)

      // Réinitialiser le bouton en cas d'erreur
      favoriteBtn.innerHTML = '<i class="fas fa-heart"></i>'

      if (error.message && error.message.includes("connecté")) {
        showNotification("Vous devez être connecté pour gérer vos favoris", "error")
        // Rediriger vers la page de connexion après un délai
        setTimeout(() => {
          const loginUrl = isInPagesFolder ? "login.php" : "pages/login.php"
          window.location.href = loginUrl + "?redirect=" + encodeURIComponent(window.location.pathname)
        }, 2000)
      } else {
        showNotification(error.message || "Erreur lors de la gestion du favori", "error")
      }
    })
    .finally(() => {
      // Réactiver le bouton
      favoriteBtn.disabled = false
    })
}

// Fallback pour éviter l'erreur updateFavoritesCount is not defined
if (typeof updateFavoritesCount !== 'function') {
  function updateFavoritesCount() {}
}
    </script>
    </body>

</html>