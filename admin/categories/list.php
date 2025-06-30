<?php

/**
 * Liste des catégories - Administration
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

// Récupérer les catégories avec le nombre de produits
$stmt = $db->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.nom ASC
");
$categories = $stmt->fetchAll();

// Messages non lus pour le sidebar
$unread_messages = 0;
try {
    $stmt = $db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'");
    $unread_messages = $stmt->fetchColumn();
} catch (Exception $e) {
    // Ignorer l'erreur si la table n'existe pas
}

// Statistiques
$total_categories = count($categories);
$total_products = 0;
foreach ($categories as $category) {
    $total_products += $category['product_count'];
}

// Variables pour le layout
$page_title = 'Gestion des Catégories';
$page_icon = 'fas fa-tags';
$header_actions = '<a href="add.php" class="btn btn-admin-primary"><i class="fas fa-plus"></i> Ajouter une catégorie</a>';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Administration Elvy.Made</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>
    /* ==============================================
   CATEGORY LIST STYLES - ADMINISTRATION
   ============================================== */

    /* Grille des catégories */
    .categories-admin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    .category-admin-card {
        background: white;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: var(--transition);
        display: flex;
        flex-direction: column;
    }

    .category-admin-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .category-admin-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--gray-50);
    }

    .category-admin-header h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--admin-dark);
        margin: 0;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .category-admin-actions {
        display: flex;
        gap: 0.5rem;
        margin-left: 1rem;
    }

    .category-admin-content {
        padding: 1.5rem;
        flex: 1;
    }

    .category-admin-image {
        margin-bottom: 1rem;
        border-radius: var(--border-radius);
        overflow: hidden;
        max-height: 180px;
    }

    .category-admin-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .category-description {
        font-size: 0.9375rem;
        color: var(--admin-dark);
        line-height: 1.5;
        margin-bottom: 1.5rem;
    }

    .category-description.text-muted {
        color: var(--text-muted);
        font-style: italic;
    }

    .category-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 1rem;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--text-muted);
    }

    .stat-item i {
        color: var(--admin-primary);
        width: 16px;
        text-align: center;
    }

    .category-admin-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border-color);
        background: var(--gray-50);
        text-align: center;
    }

    /* État vide */
    .empty-state {
        grid-column: 1 / -1;
        padding: 3rem 2rem;
        text-align: center;
        background: white;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-sm);
    }

    .empty-state i {
        font-size: 3rem;
        color: var(--gray-400);
        margin-bottom: 1.5rem;
        display: inline-block;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--admin-dark);
        margin-bottom: 0.75rem;
    }

    .empty-state p {
        font-size: 1rem;
        color: var(--text-muted);
        max-width: 500px;
        margin: 0 auto 1.5rem;
        line-height: 1.5;
    }

    /* Boutons */
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

    /* Badges */
    .badge {
        padding: 0.25rem 0.5rem;
        border-radius: var(--border-radius);
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
    }

    .badge-success {
        background: #dcfce7;
        color: #166534;
    }

    .badge-danger {
        background: #fecaca;
        color: #991b1b;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .categories-admin-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .categories-admin-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }

        .category-admin-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .category-admin-actions {
            margin-left: 0;
            width: 100%;
            justify-content: flex-end;
        }
    }

    @media (max-width: 480px) {
        .categories-admin-grid {
            grid-template-columns: 1fr;
        }

        .category-stats {
            grid-template-columns: 1fr;
        }

        .empty-state {
            padding: 2rem 1rem;
        }

        .empty-state i {
            font-size: 2.5rem;
        }

        .empty-state h3 {
            font-size: 1.25rem;
        }
    }

    /* Animations */
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

    .categories-admin-grid,
    .stats-grid {
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


                <!-- Statistiques -->
                <div class="stats-grid" >
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $total_categories; ?></h3>
                            <p>Catégories totales</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $total_products; ?></h3>
                            <p>Produits totaux</p>
                        </div>
                    </div>
                    <?php if (!empty($header_actions)): ?>
                   <div class="header-actions" style="height:60px; display: flex; align-items: center; justify-content: flex-end; padding-right: 20px; padding-top: 30px;">
                        <?php echo $header_actions; ?>
                    </div>
                <?php endif; ?>
                </div>
                
                <!-- Grille des catégories -->
                <div class="categories-admin-grid">
                    <?php if (count($categories) > 0): ?>
                        <?php foreach ($categories as $category): ?>
                            <div class="category-admin-card">
                                <div class="category-admin-header">
                                    <h3><?php echo htmlspecialchars($category['nom']); ?></h3>
                                    <div class="category-admin-actions">
                                        <a href="edit.php?id=<?php echo $category['id']; ?>"
                                            class="btn btn-sm btn-admin-primary" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($category['product_count'] == 0): ?>
                                            <a href="delete.php?id=<?php echo $category['id']; ?>"
                                                class="btn btn-sm btn-admin-danger delete-btn"
                                                title="Supprimer"
                                                data-confirm="Êtes-vous sûr de vouloir supprimer cette catégorie ?">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="category-admin-content">
                                    <?php if ($category['image']): ?>
                                        <div class="category-admin-image">
                                            <img src="../../uploads/categories/<?php echo htmlspecialchars($category['image']); ?>"
                                                alt="<?php echo htmlspecialchars($category['nom']); ?>">
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($category['description']): ?>
                                        <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                                    <?php else: ?>
                                        <p class="category-description text-muted">Aucune description</p>
                                    <?php endif; ?>

                                    <div class="category-stats">
                                        <div class="stat-item">
                                            <i class="fas fa-box"></i>
                                            <span><?php echo $category['product_count']; ?> produit<?php echo $category['product_count'] > 1 ? 's' : ''; ?></span>
                                        </div>
                                        <div class="stat-item">
                                            <i class="fas fa-calendar"></i>
                                            <span>Créée le <?php echo formatDateFrench($category['created_at'], 'd/m/Y'); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="category-admin-footer">
                                    <a href="../../pages/category.php?id=<?php echo $category['id']; ?>"
                                        class="btn btn-sm btn-admin-outline" target="_blank">
                                        <i class="fas fa-eye"></i>
                                        Voir sur le site
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-tags"></i>
                            <h3>Aucune catégorie</h3>
                            <p>Aucune catégorie n'a été créée pour le moment.</p>
                            <a href="add.php" class="btn btn-admin-primary">
                                <i class="fas fa-plus"></i>
                                Créer la première catégorie
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/admin.js"></script>
</body>

</html>