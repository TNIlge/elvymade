<?php
/**
 * Page des messages - Administration
 * ElvyMade - Site de prospection d'articles
 */

// Inclusion des fichiers de configuration
require_once '../../config/database.php';
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../includes/admin_functions.php';

// Vérifier si l'utilisateur est administrateur
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Connexion à la base de données
$db = getDBConnection();

// Traitement de la suppression d'un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message'])) {
    $message_id = intval($_POST['message_id']);
    
    $delete_stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ?");
    if ($delete_stmt->execute([$message_id])) {
        $_SESSION['success_message'] = "Le message a bien été supprimé.";
    } else {
        $_SESSION['error_message'] = "Une erreur est survenue lors de la suppression.";
    }
    header("Location: list.php");
    exit();
}

// Paramètres de pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Récupérer le nombre total de messages
$count_stmt = $db->prepare("SELECT COUNT(*) as total FROM contact_messages WHERE (user_id != ? OR user_id IS NULL)");
$count_stmt->execute([$_SESSION['user_id']]);
$total_messages = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Récupérer les messages
$stmt = $db->prepare("
    SELECT cm.*, u.nom as user_name, u.avatar as user_avatar,
           CASE 
               WHEN cm.is_from_user = 1 THEN 'sent'
               ELSE 'received'
           END as message_type
    FROM contact_messages cm
    LEFT JOIN users u ON cm.user_id = u.id
    WHERE (cm.user_id != ? OR cm.user_id IS NULL)
    ORDER BY cm.created_at DESC 
    LIMIT $per_page OFFSET $offset
");
$stmt->execute([$_SESSION['user_id']]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Marquer les messages non lus comme lus
if (!empty($messages)) {
    $message_ids = array();
    foreach ($messages as $msg) {
        if ($msg['message_type'] === 'received' && $msg['status'] === 'unread') {
            $message_ids[] = $msg['id'];
        }
    }
    
    if (!empty($message_ids)) {
        $placeholders = str_repeat('?,', count($message_ids) - 1) . '?';
        $update_stmt = $db->prepare("UPDATE contact_messages SET status = 'read' WHERE id IN ($placeholders)");
        $update_stmt->execute($message_ids);
    }
}

// Calcul de la pagination
$total_pages = ceil($total_messages / $per_page);

// Variables pour le layout
$page_title = 'Messages des Utilisateurs';
$page_icon = 'fas fa-envelope';
$current_page = 'messages/list.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Administration ElvyMade</title>
    <?php includeAdminAssets(2); ?>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include '../includes/header.php'; ?>
            
            <div class="admin-content">
                <?php if (!empty($_SESSION['success_message'])): ?>
                    <div class="alert alert-success" style="margin-bottom: var(--spacing-6);">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger" style="margin-bottom: var(--spacing-6);">
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="admin-header-actions">
                    <a href="send.php" class="btn btn-admin-primary">
                        <i class="fas fa-paper-plane"></i>
                        Envoyer un message
                    </a>
                </div>

                <!-- Statistiques -->
                <div class="admin-stats-grid">
                    <div class="admin-stat-card">
                        <div class="stat-icon bg-blue">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Messages reçus</h3>
                            <p>
                                <?php 
                                $received_stmt = $db->prepare("SELECT COUNT(*) FROM contact_messages WHERE is_from_user = 0");
                                $received_stmt->execute();
                                echo $received_stmt->fetchColumn();
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="stat-icon bg-orange">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Non lus</h3>
                            <p>
                                <?php 
                                $unread_stmt = $db->prepare("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread' AND is_from_user = 0");
                                $unread_stmt->execute();
                                echo $unread_stmt->fetchColumn();
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="stat-icon bg-green">
                            <i class="fas fa-reply"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Réponses envoyées</h3>
                            <p>
                                <?php 
                                $replied_stmt = $db->prepare("SELECT COUNT(*) FROM contact_messages WHERE status = 'replied'");
                                $replied_stmt->execute();
                                echo $replied_stmt->fetchColumn();
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="stat-icon bg-purple">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Messages envoyés</h3>
                            <p>
                                <?php 
                                $sent_stmt = $db->prepare("SELECT COUNT(*) FROM contact_messages WHERE is_from_user = 1");
                                $sent_stmt->execute();
                                echo $sent_stmt->fetchColumn();
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Liste des messages -->
                <div class="admin-card">
                    <div class="card-header">
                        <h2>
                            <i class="fas fa-inbox"></i>
                            Derniers messages
                        </h2>
                        <div class="card-actions">
                            <div class="search-box">
                                <input type="text" placeholder="Rechercher...">
                                <button><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <?php if (empty($messages)): ?>
                            <div class="empty-state">
                                <i class="fas fa-envelope-open"></i>
                                <h3>Aucun message pour le moment</h3>
                                <p>Aucun utilisateur n'a encore envoyé de message.</p>
                                <a href="send.php" class="btn btn-admin-primary">
                                    <i class="fas fa-paper-plane"></i>
                                    Envoyer un message
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="admin-table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>De/À</th>
                                            <th>Sujet</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($messages as $message): ?>
                                            <tr class="<?php echo $message['status'] === 'unread' && $message['message_type'] === 'received' ? 'unread' : ''; ?>">
                                                <td>
                                                    <div class="user-avatar">
                                                        <?php if (!empty($message['user_avatar'])): ?>
                                                            <img src="../../uploads/avatars/<?php echo htmlspecialchars($message['user_avatar']); ?>" alt="Avatar">
                                                        <?php else: ?>
                                                            <div class="avatar-placeholder">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <span>
                                                            <?php 
                                                            if ($message['message_type'] === 'received') {
                                                                echo htmlspecialchars($message['user_name'] ?? $message['nom']);
                                                            } else {
                                                                echo "À: " . htmlspecialchars($message['nom']);
                                                            }
                                                            ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($message['sujet']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $message['message_type'] === 'received' ? 'badge-primary' : 'badge-secondary'; ?>">
                                                        <?php echo $message['message_type'] === 'received' ? 'Reçu' : 'Envoyé'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatDateFrench($message['created_at'], 'd/m/Y H:i'); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $message['status']; ?>">
                                                        <?php 
                                                        switch($message['status']) {
                                                            case 'replied': echo 'Répondu'; break;
                                                            case 'read': echo 'Lu'; break;
                                                            default: echo 'Non lu'; break;
                                                        }
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="table-actions">
                                                        <?php if ($message['message_type'] === 'received'): ?>
                                                            <a href="reply.php?id=<?php echo $message['id']; ?>" class="btn-action" title="Répondre">
                                                                <i class="fas fa-reply"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="view.php?id=<?php echo $message['id']; ?>" class="btn-action" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Voulez-vous vraiment supprimer ce message ?');">
                                                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                            <button type="submit" name="delete_message" class="btn-action" title="Supprimer">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <div class="admin-pagination">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">
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
                                            <a href="?page=<?php echo $i; ?>" 
                                               class="<?php echo $i === $page ? 'active' : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>
                                    </div>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">
                                            Suivant
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <?php includeAdminScripts(2); ?>
    <script>
    // Suppression de message
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const messageId = this.getAttribute('data-id');
            const row = this.closest('tr');
            
            if (confirm('Voulez-vous vraiment supprimer ce message ? Cette action est irréversible.')) {
                const originalContent = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                this.disabled = true;
                
                fetch('delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: messageId })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                            location.reload();
                        }, 300);
                    } else {
                        throw new Error(data.message || 'Erreur lors de la suppression');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur: ' + error.message);
                    this.innerHTML = originalContent;
                    this.disabled = false;
                });
            }
        });
    });
</script>
    <style>
        .unread {
            background-color: rgba(59, 130, 246, 0.05);
            font-weight: 600;
        }
        
        .user-avatar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .user-avatar img, .avatar-placeholder {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .avatar-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gray-200);
            color: var(--gray-600);
        }
        
        .badge-primary {
            background: var(--primary-light);
            color: var(--primary-dark);
        }
        
        .badge-secondary {
            background: var(--secondary-light);
            color: var(--secondary-dark);
        }
        
        .badge-unread {
            background: #fecaca;
            color: #991b1b;
        }
        
        .badge-read {
            background: #fef3c7;
            color: #92400e;
        }
        
        .badge-replied {
            background: #dcfce7;
            color: #166534;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #166534;
            padding: var(--spacing-3) var(--spacing-4);
            border-radius: var(--border-radius);
            border-left: 4px solid #22c55e;
        }
        
        .alert-danger {
            background: #fecaca;
            color: #991b1b;
            padding: var(--spacing-3) var(--spacing-4);
            border-radius: var(--border-radius);
            border-left: 4px solid #ef4444;
        }
    </style>
</body>
</html>