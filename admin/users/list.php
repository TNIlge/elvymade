<?php

/**
 * Liste des utilisateurs - Administration
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
$role_filter = isset($_GET['role']) ? sanitizeInput($_GET['role']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$items_per_page = 20;

// Construction de la requête
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(nom LIKE ? OR prenom LIKE ? OR email LIKE ?)";
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($role_filter)) {
    $where_conditions[] = "role = ?";
    $params[] = $role_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Compter le total
$count_sql = "SELECT COUNT(*) FROM users $where_clause";
$count_stmt = $db->prepare($count_sql);
$count_stmt->execute($params);
$total_users = $count_stmt->fetchColumn();
$total_pages = ceil($total_users / $items_per_page);

// Récupérer les utilisateurs
$offset = ($page - 1) * $items_per_page;
$sql = "
    SELECT * FROM users 
    $where_clause 
    ORDER BY created_at DESC 
    LIMIT $items_per_page OFFSET $offset
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Statistiques
$stats_stmt = $db->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
        SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as users_count,
        SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as recent
    FROM users
");
$stats = $stats_stmt->fetch();

// Messages non lus pour le sidebar
$unread_messages = 0;
try {
    $stmt = $db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'");
    $unread_messages = $stmt->fetchColumn();
} catch (Exception $e) {
    // Ignorer l'erreur si la table n'existe pas
}

// Variables pour le layout
$page_title = 'Gestion des Utilisateurs';
$page_icon = 'fas fa-users';
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
   USER MANAGEMENT STYLES - ADMINISTRATION
   ============================================== */

/* Styles des statistiques */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}

.stat-card.primary { border-left-color: var(--admin-primary); }
.stat-card.success { border-left-color: var(--admin-success); }
.stat-card.warning { border-left-color: var(--admin-warning); }
.stat-card.info { border-left-color: var(--admin-info); }

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
.stat-icon.info { background: var(--admin-info); }

.stat-content h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--admin-dark);
    margin-bottom: 0.25rem;
    line-height: 1.2;
}

.stat-content p {
    color: var(--text-muted);
    font-size: 0.875rem;
    margin-bottom: 0;
}

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

/* Filtres */
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

/* Tableau des utilisateurs */
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

/* Informations utilisateur */
.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    flex-shrink: 0;
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-details strong {
    font-weight: 600;
}

/* Badges */
.badge {
    padding: 0.375rem 0.5rem;
    border-radius: var(--border-radius);
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
    text-align: center;
    min-width: 100px;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.badge-info {
    background: #dbeafe;
    color: #1e40af;
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

.btn-admin-primary {
    background: var(--admin-primary);
    color: white;
}

.btn-admin-primary:hover {
    background: var(--admin-primary-dark);
    color: white;
}

.btn-admin-warning {
    background: var(--admin-warning);
    color: white;
}

.btn-admin-warning:hover {
    background: var(--admin-warning-dark);
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
    
    .stats-grid {
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
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .admin-table td {
        font-size: 0.875rem;
    }
    
    .badge {
        min-width: auto;
        padding: 0.25rem 0.375rem;
    }
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.admin-table-container,
.filters-container,
.admin-pagination {
    animation: fadeIn 0.4s ease-out;
}

/* Modal de confirmation */
.confirm-modal {
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

.confirm-modal.active {
    opacity: 1;
    visibility: visible;
}

.confirm-modal-content {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: 2rem;
    max-width: 500px;
    width: 90%;
    box-shadow: var(--shadow-lg);
    transform: translateY(20px);
    transition: var(--transition);
}

.confirm-modal.active .confirm-modal-content {
    transform: translateY(0);
}

.confirm-modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--admin-dark);
}

.confirm-modal-actions {
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
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['total']; ?></h3>
                            <p>Utilisateurs totaux</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['users_count']; ?></h3>
                            <p>Utilisateurs standards</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['admins']; ?></h3>
                            <p>Administrateurs</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon info">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['recent']; ?></h3>
                            <p>Nouveaux (30j)</p>
                        </div>
                    </div>
                </div>

                <!-- Actions et filtres -->
                <div class="content-header">
                    <div class="content-title">
                        <h2>Liste des Utilisateurs</h2>
                        <p><?php echo $total_users; ?> utilisateur<?php echo $total_users > 1 ? 's' : ''; ?> au total</p>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="filters-container">
                    <form method="GET" action="list.php" class="filters-form">
                        <div class="filter-group">
                            <input type="text" name="search" placeholder="Rechercher un utilisateur..."
                                value="<?php echo htmlspecialchars($search); ?>" class="filter-input">
                        </div>

                        <div class="filter-group">
                            <select name="role" class="filter-select">
                                <option value="">Tous les rôles</option>
                                <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>Utilisateur</option>
                                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
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

                <!-- Tableau des utilisateurs -->
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nom complet</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Ville</th>
                                <th>Rôle</th>
                                <th>Date d'inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <?php echo strtoupper(
                                                        (isset($user['prenom']) ? substr($user['prenom'], 0, 1) : '') .
                                                            (isset($user['nom']) ? substr($user['nom'], 0, 1) : '')
                                                    ); ?> </div>
                                                <div class="user-details">
                                                    <strong><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['telephone'] ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($user['ville'] ?: '-'); ?></td>
                                        <td>
                                            <?php if ($user['role'] === 'admin'): ?>
                                                <span class="badge badge-warning">Administrateur</span>
                                            <?php else: ?>
                                                <span class="badge badge-info">Utilisateur</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatDateFrench($user['created_at'], 'd/m/Y H:i'); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="view.php?id=<?php echo $user['id']; ?>"
                                                    class="btn btn-sm btn-admin-primary" title="Voir le profil">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <button onclick="toggleUserRole(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>')"
                                                        class="btn btn-sm btn-admin-warning" title="Changer le rôle">
                                                        <i class="fas fa-user-cog"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="empty-state">
                                            <i class="fas fa-users"></i>
                                            <h3>Aucun utilisateur trouvé</h3>
                                            <p>Aucun utilisateur ne correspond à vos critères de recherche.</p>
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
    <script>
        function toggleUserRole(userId, currentRole) {
            const newRole = currentRole === 'admin' ? 'user' : 'admin';
            const message = `Êtes-vous sûr de vouloir changer le rôle de cet utilisateur en "${newRole === 'admin' ? 'Administrateur' : 'Utilisateur'}" ?`;

            if (confirm(message)) {
                fetch('../../api/users.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=change_role&user_id=${userId}&new_role=${newRole}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erreur: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue');
                    });
            }
        }
    </script>
</body>

</html>