<?php
/**
 * Page des favoris utilisateur
 * ElvyMade - Site de prospection d'articles
 */

// Inclusion des fichiers de configuration
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Connexion à la base de données
$db = getDBConnection();

// Paramètres de pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Récupérer le nombre total de favoris
$count_stmt = $db->prepare("SELECT COUNT(*) as total FROM favorites WHERE user_id = ?");
$count_stmt->execute([$_SESSION['user_id']]);
$total_favorites = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Récupérer les favoris avec les détails des produits
$stmt = $db->prepare("
    SELECT f.*, p.id as product_id, p.nom as product_name, p.description, p.prix, p.image, p.status, p.disponible, p.ville,
           c.nom as category_name
    FROM favorites f
    JOIN products p ON f.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
    LIMIT $per_page OFFSET $offset
");
$stmt->execute([$_SESSION['user_id']]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul de la pagination
$total_pages = ceil($total_favorites / $per_page);

$_SESSION['favorites_count'] = $total_favorites;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Favoris - Elvy.Made</title>
    <meta name="description" content="Consultez vos produits favoris sur ElvyMade.">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="logged-in">
    <?php include '../includes/header.php'; ?>
    
    <div class="container" style="padding: var(--spacing-8) var(--spacing-4);">
        <!-- Fil d'Ariane -->
        <nav style="margin-bottom: var(--spacing-8);">
            <a href="../index.php" style="color: var(--gray-600); text-decoration: none;">Accueil</a>
            <span style="margin: 0 var(--spacing-2); color: var(--gray-400);">/</span>
            <a href="profile.php" style="color: var(--gray-600); text-decoration: none;">Mon Profil</a>
            <span style="margin: 0 var(--spacing-2); color: var(--gray-400);">/</span>
            <span style="color: var(--primary-color); font-weight: 600;">Mes Favoris</span>
        </nav>

        <!-- En-tête des favoris -->
        <div style="text-align: center; margin-bottom: var(--spacing-8);">
            <h1 style="font-size: var(--font-size-4xl); color: var(--primary-color); margin-bottom: var(--spacing-3); display: flex; align-items: center; justify-content: center; gap: var(--spacing-3);">
                <i class="fas fa-heart"></i>
                Mes Favoris
            </h1>
            <p style="font-size: var(--font-size-lg); color: var(--gray-600);">
                <?php if ($total_favorites > 0): ?>
                    Vous avez <?php echo $total_favorites; ?> produit<?php echo $total_favorites > 1 ? 's' : ''; ?> en favoris
                <?php else: ?>
                    Vous n'avez pas encore de favoris
                <?php endif; ?>
            </p>
        </div>
        
        <?php if (empty($favorites)): ?>
            <!-- Aucun favori -->
            <div style="text-align: center; padding: var(--spacing-12) 0; background: var(--gray-50); border-radius: var(--border-radius-xl); margin: var(--spacing-6) 0;">
                <div style="font-size: var(--font-size-5xl); color: var(--primary-light); margin-bottom: var(--spacing-4);">
                    <i class="fas fa-heart-broken"></i>
                </div>
                <h3 style="font-size: var(--font-size-2xl); color: var(--gray-800); margin-bottom: var(--spacing-2);">
                    Aucun favori pour le moment
                </h3>
                <p style="color: var(--gray-600); margin-bottom: var(--spacing-6); max-width: 500px; margin-left: auto; margin-right: auto;">
                    Explorez nos produits et ajoutez vos coups de cœur à vos favoris pour les retrouver facilement.
                </p>
                <div style="display: flex; gap: var(--spacing-4); justify-content: center;">
                    <a href="../index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i>
                        Découvrir les produits
                    </a>
                    <a href="search.php" class="btn btn-outline">
                        <i class="fas fa-search"></i>
                        Rechercher
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Grille des favoris -->
            <div class="favorites-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: var(--spacing-6); margin-bottom: var(--spacing-8);">
                <?php foreach ($favorites as $favorite): ?>
                    <div class="favorite-item product-card" data-product-id="<?php echo $favorite['product_id']; ?>" style="background: var(--white); border-radius: var(--border-radius-lg); overflow: hidden; box-shadow: var(--shadow); transition: var(--transition); position: relative;">
                        <!-- Image du produit -->
                        <div class="product-image" style="position: relative; width: 100%; height: 200px; overflow: hidden;">
                            <?php if (!empty($favorite['image'])): ?>
                                <img src="../uploads/products/<?php echo htmlspecialchars($favorite['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($favorite['product_name']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: var(--gray-100); color: var(--gray-400); font-size: var(--font-size-2xl);">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Bouton favori -->
                            <button class="favorite-btn active" 
                                    onclick="toggleFavorite(<?php echo $favorite['product_id']; ?>)" 
                                    data-product-id="<?php echo $favorite['product_id']; ?>"
                                    style="position: absolute; top: var(--spacing-3); right: var(--spacing-3); width: 36px; height: 36px; border-radius: 50%; background: var(--primary-color); border: none; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow); transition: var(--transition);">
                                <i class="fas fa-heart"></i>
                            </button>
                            
                            <!-- Badge de statut -->
                            <?php if (!$favorite['disponible']): ?>
                                <div style="position: absolute; top: var(--spacing-3); left: var(--spacing-3); background: var(--error-color); color: white; padding: var(--spacing-1) var(--spacing-2); border-radius: var(--border-radius); font-size: var(--font-size-xs); font-weight: 600;">
                                    Non disponible
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Informations du produit -->
                        <div style="padding: var(--spacing-4);">
                            <div style="color: var(--primary-color); font-size: var(--font-size-sm); font-weight: 600; margin-bottom: var(--spacing-2); text-transform: uppercase; letter-spacing: 0.5px;">
                                <?php echo htmlspecialchars($favorite['category_name']); ?>
                            </div>
                            
                            <h3 style="font-size: var(--font-size-lg); font-weight: 600; margin-bottom: var(--spacing-2); color: var(--gray-900); line-height: 1.4;">
                                <a href="product.php?id=<?php echo $favorite['product_id']; ?>" 
                                   style="text-decoration: none; color: inherit; transition: color 0.3s ease;">
                                    <?php echo htmlspecialchars($favorite['product_name']); ?>
                                </a>
                            </h3>
                            
                            <p style="color: var(--gray-600); font-size: var(--font-size-sm); margin-bottom: var(--spacing-3); line-height: 1.5;">
                                <?php echo htmlspecialchars(substr($favorite['description'], 0, 100)) . (strlen($favorite['description']) > 100 ? '...' : ''); ?>
                            </p>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-4);">
                                <div style="font-size: var(--font-size-xl); font-weight: 700; color: var(--primary-color);">
                                    <?php echo formatPrice($favorite['prix']); ?>
                                </div>
                                
                                <?php if (!empty($favorite['ville'])): ?>
                                    <div style="color: var(--gray-500); font-size: var(--font-size-sm); display: flex; align-items: center; gap: var(--spacing-1);">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($favorite['ville']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div style="display: flex; gap: var(--spacing-2);">
                                <a href="product.php?id=<?php echo $favorite['product_id']; ?>" 
                                   class="btn btn-primary" 
                                   style="flex: 1; display: flex; align-items: center; justify-content: center; gap: var(--spacing-2); padding: var(--spacing-2) var(--spacing-3); font-size: var(--font-size-sm);">
                                    <i class="fas fa-eye"></i>
                                    Voir
                                </a>
                                
                                <a href="#" 
                                   onclick="contactWhatsApp(<?php echo $favorite['product_id']; ?>, '<?php echo addslashes($favorite['product_name']); ?>', '<?php echo formatPrice($favorite['prix']); ?>'); return false;" 
                                   class="btn btn-success" 
                                   style="flex: 1; display: flex; align-items: center; justify-content: center; gap: var(--spacing-2); padding: var(--spacing-2) var(--spacing-3); font-size: var(--font-size-sm); background: #25D366; border-color: #25D366;">
                                    <i class="fab fa-whatsapp"></i>
                                    WhatsApp
                                </a>
                            </div>
                        </div>
                        
                        <!-- Date d'ajout -->
                        <div style="padding: var(--spacing-2) var(--spacing-4); background: var(--gray-50); border-top: 1px solid var(--gray-200); color: var(--gray-500); font-size: var(--font-size-xs); text-align: center;">
                            <i class="fas fa-heart" style="color: var(--primary-color);"></i>
                            Ajouté le <?php echo formatDateFrench($favorite['created_at'], 'd/m/Y'); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination" style="display: flex; justify-content: center; align-items: center; gap: var(--spacing-2); margin-top: var(--spacing-8);">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn" style="display: flex; align-items: center; gap: var(--spacing-2); padding: var(--spacing-2) var(--spacing-4); background: var(--white); border: 1px solid var(--gray-300); border-radius: var(--border-radius); color: var(--gray-700); text-decoration: none; transition: var(--transition);">
                            <i class="fas fa-chevron-left"></i>
                            Précédent
                        </a>
                    <?php endif; ?>
                    
                    <div class="pagination-numbers" style="display: flex; gap: var(--spacing-1);">
                        <?php
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="pagination-number <?php echo $i === $page ? 'active' : ''; ?>"
                               style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: var(--border-radius); text-decoration: none; transition: var(--transition); <?php echo $i === $page ? 'background: var(--primary-color); color: white;' : 'background: var(--white); border: 1px solid var(--gray-300); color: var(--gray-700);'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn" style="display: flex; align-items: center; gap: var(--spacing-2); padding: var(--spacing-2) var(--spacing-4); background: var(--white); border: 1px solid var(--gray-300); border-radius: var(--border-radius); color: var(--gray-700); text-decoration: none; transition: var(--transition);">
                            Suivant
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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
    
    <script>
        // Fonction pour contacter via WhatsApp avec les détails du produit
        function contactWhatsApp(productId, productName, productPrice) {
            const message = `Bonjour ! Je suis intéressé(e) par ce produit de mes favoris :\n\n` +
                          `📦 ${productName}\n` +
                          `💰 ${productPrice}\n\n` +
                          `Pouvez-vous me donner plus d'informations ?`;
            
            const whatsappUrl = `https://wa.me/237658470529?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
        
        // Animation au survol des cartes
        document.querySelectorAll('.favorite-item').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = 'var(--shadow-lg)';
                
                const img = this.querySelector('.product-image img');
                if (img) {
                    img.style.transform = 'scale(1.05)';
                }
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'var(--shadow)';
                
                const img = this.querySelector('.product-image img');
                if (img) {
                    img.style.transform = 'scale(1)';
                }
            });
        });
        // Modifier la fonction d'animation au survol
document.querySelectorAll('.favorite-item').forEach(card => {
    // Garder l'animation de survol
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
        this.style.boxShadow = 'var(--shadow-lg)';
        
        const img = this.querySelector('.product-image img');
        if (img) {
            img.style.transform = 'scale(1.05)';
        }
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = 'var(--shadow)';
        
        const img = this.querySelector('.product-image img');
        if (img) {
            img.style.transform = 'scale(1)';
        }
    });
    
    // Empêcher la propagation des clics pour les boutons
    card.querySelectorAll('button, .btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
});
        // Animation de suppression des favoris
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; transform: scale(1); }
                to { opacity: 0; transform: scale(0.8); }
            }
        `;
        document.head.appendChild(style);
    </script>
    
    <style>
        /* Assurer que les éléments cliquables sont accessibles */
.product-card {
    position: relative;
    overflow: hidden;
}

.product-card a {
    position: relative;
    z-index: 1;
}

.product-image {
    position: relative;
    z-index: 1;
}

.favorite-btn {
    position: absolute;
    z-index: 10 !important;
    cursor: pointer;
}

.btn {
    cursor: pointer;
    position: relative;
    z-index: 2;
}

/* Empêcher les événements de pointer sur l'image pour permettre les clics */
.product-image img {
    pointer-events: none;
}

/* Animation pour les interactions */
.btn-primary:hover, 
.btn-success:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2) !important;
}

/* Correction pour le bouton WhatsApp */
.btn-success {
    pointer-events: auto !important;
}
        .btn-success {
            background: #25D366;
            border-color: #25D366;
            color: white;
        }
        
        .btn-success:hover {
            background: #1DA851;
            border-color: #1DA851;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(37, 211, 102, 0.3);
        }
        
        .pagination-btn:hover,
        .pagination-number:hover:not(.active) {
            background: var(--gray-100);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .favorites-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)) !important;
                gap: var(--spacing-4) !important;
            }
            
            .pagination {
                flex-wrap: wrap;
                gap: var(--spacing-1) !important;
            }
            
            .pagination-btn {
                font-size: var(--font-size-sm);
                padding: var(--spacing-1) var(--spacing-2) !important;
            }
        }
        
    </style>
</body>
</html>
