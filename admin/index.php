<?php
/**
 * Tableau de bord administrateur
 * ElvyMade - Site de prospection d'articles
 */

// Inclusion des fichiers de configuration
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once 'includes/admin_functions.php';

// Vérifier si l'utilisateur est administrateur
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// Connexion à la base de données
$db = getDBConnection();

// Récupérer les statistiques
$stmt = $db->query("SELECT COUNT(*) FROM products");
$total_products = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$total_users = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM categories");
$total_categories = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'");
$unread_messages = $stmt->fetchColumn();

// Produits récemment ajoutés
$stmt = $db->query("
    SELECT p.*, c.nom as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$recent_products = $stmt->fetchAll();

// Utilisateurs récemment inscrits
$stmt = $db->query("
    SELECT nom, prenom, email, created_at 
    FROM users 
    WHERE role = 'user' 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recent_users = $stmt->fetchAll();

// Messages de contact récents
$stmt = $db->query("
    SELECT nom, email, sujet, created_at, status 
    FROM contact_messages 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recent_messages = $stmt->fetchAll();

// Statistiques par catégorie
$stmt = $db->query("
    SELECT c.nom, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id, c.nom 
    ORDER BY product_count DESC
");
$category_stats = $stmt->fetchAll();

// Variables pour le layout
$page_title = 'Tableau de bord';
$page_icon = 'fas fa-chart-bar';
$current_page = 'index.php';
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
    <?php includeAdminAssets(1); ?>
</head>
<style>
    /* ==============================================
   DASHBOARD STYLES - ADMINISTRATION
   ============================================== */

/* Layout général amélioré */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

/* Cartes de statistiques - Version desktop */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: var(--transition);
    border-left: 4px solid transparent;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}

.stat-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--admin-primary);
    opacity: 0;
    transition: var(--transition);
}

.stat-card:hover::after {
    opacity: 1;
}

.stat-card.primary { border-left-color: var(--admin-primary); }
.stat-card.success { border-left-color: var(--admin-success); }
.stat-card.warning { border-left-color: var(--admin-warning); }
.stat-card.danger { border-left-color: var(--admin-danger); }

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.stat-icon.primary { background: var(--admin-primary); }
.stat-icon.success { background: var(--admin-success); }
.stat-icon.warning { background: var(--admin-warning); }
.stat-icon.danger { background: var(--admin-danger); }

.stat-content h3 {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--admin-dark);
    margin-bottom: 0.25rem;
    line-height: 1.2;
}

.stat-content p {
    color: var(--text-muted);
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

/* Version mobile compacte */
.compact-stats {
    display: none;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.compact-stat {
    background: white;
    padding: 1rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    text-align: center;
}

.compact-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--admin-primary);
    margin-bottom: 0.25rem;
}

.compact-stat-label {
    font-size: 0.75rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Statistiques par catégorie */
.category-stats {
    margin-top: 1rem;
}

.category-stat-item {
    margin-bottom: 1rem;
}

.category-stat-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.category-name {
    font-weight: 500;
    color: var(--admin-dark);
}

.category-count {
    color: var(--text-muted);
}

.category-stat-bar {
    height: 8px;
    background: var(--gray-100);
    border-radius: 4px;
    overflow: hidden;
}

.category-stat-progress {
    height: 100%;
    background: var(--admin-primary);
    border-radius: 4px;
    transition: width 1s ease;
}

/* Tableaux améliorés */
.admin-table-container {
    background: white;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.admin-table-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.admin-table-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--admin-dark);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.admin-table-title i {
    color: var(--admin-primary);
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
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
.product-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.product-thumb {
    width: 40px;
    height: 40px;
    border-radius: 4px;
    object-fit: cover;
    border: 1px solid var(--border-color);
}

/* Badges */
.badge {
    padding: 0.25rem 0.5rem;
    border-radius: var(--border-radius);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: inline-block;
}

.badge-success {
    background: #dcfce7;
    color: #166534;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.badge-danger {
    background: #fecaca;
    color: #991b1b;
}

/* Actions rapides */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.quick-action {
    background: white;
    padding: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    text-align: center;
    text-decoration: none;
    color: var(--admin-dark);
    transition: var(--transition);
    display: flex;
    flex-direction: column;
    align-items: center;
}

.quick-action:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
    color: var(--admin-primary);
}

.quick-action-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(79, 70, 229, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    color: var(--admin-primary);
    font-size: 1.25rem;
    transition: var(--transition);
}

.quick-action:hover .quick-action-icon {
    background: var(--admin-primary);
    color: white;
}

.quick-action-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.quick-action-desc {
    font-size: 0.8125rem;
    color: var(--text-muted);
    line-height: 1.4;
}

/* Responsive */
@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        display: none;
    }
    
    .compact-stats {
        display: grid;
    }
    
    .admin-table-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem;
    }
    
    .quick-actions {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .quick-actions {
        grid-template-columns: 1fr;
    }
    
    .admin-table td, 
    .admin-table th {
        padding: 0.75rem;
    }
    
    .product-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.chart-container,
.admin-table-container,
.quick-actions {
    animation: fadeIn 0.5s ease-out;
}

/* Effet de chargement */
.skeleton-loading {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
    color: transparent;
}

@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="admin-content">
                <!-- Cartes de statistiques -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $total_products; ?></h3>
                            <p>Produits totaux</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $total_users; ?></h3>
                            <p>Utilisateurs</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $total_categories; ?></h3>
                            <p>Catégories</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon danger">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $unread_messages; ?></h3>
                            <p>Messages non lus</p>
                        </div>
                    </div>
                </div>

                <!-- Version compacte pour mobile -->
                <div class="compact-stats">
                    <div class="compact-stat">
                        <div class="compact-stat-value"><?php echo $total_products; ?></div>
                        <div class="compact-stat-label">Produits totaux</div>
                    </div>
                    <div class="compact-stat">
                        <div class="compact-stat-value"><?php echo $total_users; ?></div>
                        <div class="compact-stat-label">Utilisateurs</div>
                    </div>
                    <div class="compact-stat">
                        <div class="compact-stat-value"><?php echo $total_categories; ?></div>
                        <div class="compact-stat-label">Catégories</div>
                    </div>
                    <div class="compact-stat">
                        <div class="compact-stat-value"><?php echo $unread_messages; ?></div>
                        <div class="compact-stat-label">Messages non lus</div>
                    </div>
                </div>

                <!-- Graphiques et tableaux -->
                <div class="dashboard-grid">
                    <!-- Statistiques par catégorie -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="chart-title">Produits par catégorie</h3>
                        </div>
                        <div class="category-stats">
                            <?php foreach ($category_stats as $stat): ?>
                                <div class="category-stat-item">
                                    <div class="category-stat-info">
                                        <span class="category-name"><?php echo htmlspecialchars($stat['nom']); ?></span>
                                        <span class="category-count"><?php echo $stat['product_count']; ?> produits</span>
                                    </div>
                                    <div class="category-stat-bar">
                                        <div class="category-stat-progress" style="width: <?php echo $total_products > 0 ? ($stat['product_count'] / $total_products * 100) : 0; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Produits récents -->
                    <div class="admin-table-container">
                        <div class="admin-table-header">
                            <h3 class="admin-table-title">Produits récents</h3>
                            <a href="products/list.php" class="btn btn-admin-primary btn-sm">Voir tout</a>
                        </div>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Catégorie</th>
                                    <th>Prix</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_products as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="product-info">
                                                <img src="../uploads/products/<?php echo $product['image'] ?: 'placeholder.jpg'; ?>" 
                                                     alt="<?php echo htmlspecialchars($product['nom']); ?>" 
                                                     class="product-thumb">
                                                <span><?php echo htmlspecialchars($product['nom']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td><?php echo formatPrice($product['prix']); ?></td>
                                        <td><?php echo formatDateFrench($product['created_at'], 'd/m/Y'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="dashboard-grid">
                    <!-- Utilisateurs récents -->
                    <div class="admin-table-container">
                        <div class="admin-table-header">
                            <h3 class="admin-table-title">Nouveaux utilisateurs</h3>
                            <a href="users/list.php" class="btn btn-admin-primary btn-sm">Voir tout</a>
                        </div>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Date d'inscription</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo formatDateFrench($user['created_at'], 'd/m/Y'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Messages récents -->
                    <div class="admin-table-container">
                        <div class="admin-table-header">
                            <h3 class="admin-table-title">Messages récents</h3>
                            <a href="messages/list.php" class="btn btn-admin-primary btn-sm">Voir tout</a>
                        </div>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Sujet</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_messages as $message): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($message['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($message['sujet']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $message['status'] === 'unread' ? 'badge-warning' : 'badge-success'; ?>">
                                                <?php echo $message['status'] === 'unread' ? 'Non lu' : 'Lu'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDateFrench($message['created_at'], 'd/m/Y'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="quick-actions">
                    <a href="products/add.php" class="quick-action">
                        <div class="quick-action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="quick-action-title">Ajouter un produit</div>
                        <div class="quick-action-desc">Créer un nouveau produit</div>
                    </a>
                    
                    <a href="categories/add.php" class="quick-action">
                        <div class="quick-action-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div class="quick-action-title">Nouvelle catégorie</div>
                        <div class="quick-action-desc">Créer une catégorie</div>
                    </a>
                    
                    <a href="messages/list.php" class="quick-action">
                        <div class="quick-action-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="quick-action-title">Messages</div>
                        <div class="quick-action-desc">Gérer les messages</div>
                    </a>
                    
                    <a href="../index.php" class="quick-action">
                        <div class="quick-action-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="quick-action-title">Voir le site</div>
                        <div class="quick-action-desc">Aperçu du site</div>
                    </a>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Menu mobile -->
    <div class="mobile-sidebar"></div>
    <div class="overlay"></div>
    
    <?php includeAdminScripts(1); ?>
</body>
</html>
