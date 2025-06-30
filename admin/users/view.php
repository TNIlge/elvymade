<?php

/**
 * Voir un utilisateur - Administration
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

// Vérifier si l'ID de l'utilisateur est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('list.php');
}

$user_id = (int)$_GET['id'];

// Connexion à la base de données
$db = getDBConnection();

// Récupérer l'utilisateur
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    redirect('list.php');
}

/**
 * Génère les initiales à afficher quand il n'y a pas d'avatar
 */
function generateInitials($user) {
    $initials = '';
    if (!empty($user['prenom'])) {
        $initials .= strtoupper(substr($user['prenom'], 0, 1));
    }
    $initials .= strtoupper(substr($user['nom'], 0, 1));
    return $initials;
}

// Récupérer les favoris de l'utilisateur
$favorites_stmt = $db->prepare("
    SELECT p.*, c.nom as category_name, f.created_at as favorited_at
    FROM products p 
    JOIN favorites f ON p.id = f.product_id 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE f.user_id = ? 
    ORDER BY f.created_at DESC 
    LIMIT 10
");
$favorites_stmt->execute([$user_id]);
$favorites = $favorites_stmt->fetchAll();

// Compter les favoris
$favorites_count_stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
$favorites_count_stmt->execute([$user_id]);
$favorites_count = $favorites_count_stmt->fetchColumn();

// Messages non lus pour le sidebar
$unread_messages = 0;
try {
    $stmt = $db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'");
    $unread_messages = $stmt->fetchColumn();
} catch (Exception $e) {
    // Ignorer l'erreur si la table n'existe pas
}

// Statistiques de l'utilisateur
$stats = [
    'favorites_count' => $favorites_count,
    'registration_date' => $user['created_at']
];

// Variables pour le layout
$page_title = 'Profil Utilisateur';
$page_icon = 'fas fa-user';
$header_actions = '<a href="list.php" class="btn btn-admin-outline"><i class="fas fa-arrow-left"></i> Retour à la liste</a>';
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
    <style>
        /* Styles spécifiques pour la page de profil */
        .user-profile-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }

        .user-profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .user-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
            font-weight: bold;
            margin-right: 20px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .user-avatar-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-profile-info {
            flex-grow: 1;
        }

        .user-profile-info h2 {
            margin: 0 0 5px 0;
            color: #333;
        }

        .user-email {
            color: #666;
            margin: 0 0 10px 0;
        }

        .user-profile-actions {
            margin-left: auto;
        }

        .user-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .user-details-grid {
                grid-template-columns: 1fr;
            }
            .user-avatar-large {
                width: 80px;
                height: 80px;
                font-size: 28px;
            }
            .user-profile-header {
                flex-direction: column;
                text-align: center;
            }
            .user-profile-actions {
                margin: 15px 0 0 0;
            }
        }

        .user-details-card, .user-stats-card {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .user-details-card h3, .user-stats-card h3 {
            margin-top: 0;
            color: #444;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .detail-item {
            margin-bottom: 10px;
            display: flex;
        }

        .detail-item strong {
            width: 150px;
            color: #666;
        }

        .detail-item span {
            flex-grow: 1;
            color: #333;
        }

        .stats-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .stat-item {
            display: flex;
            align-items: center;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            background: #e6f7ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #1890ff;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
        }

        .stat-value {
            font-weight: bold;
            font-size: 18px;
            color: #333;
        }

        .stat-label {
            font-size: 13px;
            color: #666;
        }

        .user-favorites-section {
            margin-top: 30px;
        }

        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .favorite-item {
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s;
        }

        .favorite-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .favorite-image {
            height: 150px;
            overflow: hidden;
        }

        .favorite-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .favorite-info {
            padding: 15px;
        }

        .favorite-info h4 {
            margin: 0 0 5px 0;
            color: #333;
        }

        .favorite-category {
            font-size: 13px;
            color: #666;
            margin: 0 0 5px 0;
        }

        .favorite-price {
            font-weight: bold;
            color: #1890ff;
            margin: 0 0 5px 0;
        }

        .favorite-date {
            font-size: 12px;
            color: #999;
            margin: 0;
        }

        .favorite-actions {
            padding: 0 15px 15px 15px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #888;
        }

        .empty-state i {
            font-size: 50px;
            margin-bottom: 20px;
            color: #ddd;
        }

        .empty-state h4 {
            margin: 0 0 10px 0;
            color: #666;
        }
        .user-avatar-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6e8efb, #a777e3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: white;
    font-weight: bold;
    margin-right: 20px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    position: relative;
}

.user-avatar-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="admin-main">
            <?php include '../includes/header.php'; ?>

            <div class="admin-content">
                <!-- Profil utilisateur -->
                <div class="user-profile-container">
                    <div class="user-profile-header">
                     <div class="user-avatar-large">
    <?php 
    // Chemin vers le dossier des avatars
    $avatarPath = '../../uploads/avatars/';
    $avatarFile = htmlspecialchars($user['avatar'] ?? '');
    
    // Vérifier si l'avatar existe
    if (!empty($avatarFile) && file_exists($avatarPath . $avatarFile)): 
    ?>
        <img src="<?php echo $avatarPath . $avatarFile; ?>"
            alt="Avatar de <?php echo htmlspecialchars(($user['prenom'] ?? '') . ' ' . $user['nom']); ?>"
            onerror="this.onerror=null;this.parentElement.innerHTML='<?php echo generateInitials($user); ?>'">
    <?php else: ?>
        <?php echo generateInitials($user); ?>
    <?php endif; ?>
</div>
                        <div class="user-profile-info">
                            <h2><?php echo htmlspecialchars(($user['prenom'] ?? '') . ' ' . $user['nom']); ?></h2>
                            <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                            <div class="user-role">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-user-shield"></i>
                                        Administrateur
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-info">
                                        <i class="fas fa-user"></i>
                                        Utilisateur
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="user-profile-actions">
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <button onclick="toggleUserRole(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>')"
                                    class="btn btn-admin-warning">
                                    <i class="fas fa-user-cog"></i>
                                    Changer le rôle
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Informations détaillées -->
                    <div class="user-details-grid">
                        <div class="user-details-card">
                            <h3>Informations personnelles</h3>
                            <div class="detail-item">
                                <strong>Nom complet :</strong>
                                <span><?php echo htmlspecialchars(($user['prenom'] ?? '') . ' ' . $user['nom']); ?></span>
                            </div>
                            <div class="detail-item">
                                <strong>Email :</strong>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="detail-item">
                                <strong>Téléphone :</strong>
                                <span><?php echo htmlspecialchars($user['telephone'] ?: 'Non renseigné'); ?></span>
                            </div>
                            <div class="detail-item">
                                <strong>Ville :</strong>
                                <span><?php echo htmlspecialchars($user['ville'] ?: 'Non renseignée'); ?></span>
                            </div>
                            <div class="detail-item">
                                <strong>Date d'inscription :</strong>
                                <span><?php echo formatDateFrench($user['created_at'], 'd/m/Y à H:i'); ?></span>
                            </div>
                        </div>

                        <div class="user-stats-card">
                            <h3>Statistiques</h3>
                            <div class="stats-list">
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <div class="stat-info">
                                        <span class="stat-value"><?php echo $stats['favorites_count']; ?></span>
                                        <span class="stat-label">Favoris</span>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div class="stat-info">
                                        <span class="stat-value"><?php echo floor((time() - strtotime($user['created_at'])) / (60 * 60 * 24)); ?></span>
                                        <span class="stat-label">Jours depuis l'inscription</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Favoris récents -->
                    <?php if (count($favorites) > 0): ?>
                        <div class="user-favorites-section">
                            <h3>Favoris récents</h3>
                            <div class="favorites-grid">
                                <?php foreach ($favorites as $favorite): ?>
                                    <div class="favorite-item">
                                        <div class="favorite-image">
                                            <img src="../../uploads/products/<?php echo $favorite['image'] ?: 'placeholder.jpg'; ?>"
                                                alt="<?php echo htmlspecialchars($favorite['nom']); ?>">
                                        </div>
                                        <div class="favorite-info">
                                            <h4><?php echo htmlspecialchars($favorite['nom']); ?></h4>
                                            <p class="favorite-category"><?php echo htmlspecialchars($favorite['category_name']); ?></p>
                                            <p class="favorite-price"><?php echo formatPrice($favorite['prix']); ?></p>
                                            <p class="favorite-date">Ajouté le <?php echo formatDateFrench($favorite['favorited_at'], 'd/m/Y'); ?></p>
                                        </div>
                                        <div class="favorite-actions">
                                            <a href="../../pages/product.php?id=<?php echo $favorite['id']; ?>"
                                                class="btn btn-sm btn-admin-primary" target="_blank">
                                                <i class="fas fa-eye"></i>
                                                Voir
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($favorites_count > 10): ?>
                                <div class="text-center">
                                    <p>Et <?php echo ($favorites_count - 10); ?> autre<?php echo ($favorites_count - 10) > 1 ? 's' : ''; ?> favori<?php echo ($favorites_count - 10) > 1 ? 's' : ''; ?>...</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="user-favorites-section">
                            <h3>Favoris</h3>
                            <div class="empty-state">
                                <i class="fas fa-heart"></i>
                                <h4>Aucun favori</h4>
                                <p>Cet utilisateur n'a pas encore ajouté de produits à ses favoris.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
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
                            alert('Le rôle a été modifié avec succès !');
                            window.location.reload();
                        } else {
                            alert('Erreur : ' + (data.message || 'Une erreur est survenue'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Une erreur est survenue lors de la modification du rôle');
                    });
            }
        }
    </script>
</body>
</html>