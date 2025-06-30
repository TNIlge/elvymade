<?php
/**
 * Voir un message - Administration
 * ElvyMade - Site de prospection d'articles
 */

// Inclusion des fichiers de configuration
require_once '../../config/database.php';
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../includes/admin_functions.php';

// Vérifier si l'utilisateur est administrateur
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Vérifier si l'ID du message est fourni
if (empty($_GET['id']) || !ctype_digit($_GET['id'])) {
    redirect('list.php');
}

$message_id = (int)$_GET['id'];

// Connexion à la base de données
$db = getDBConnection();

// Récupérer le message avec les infos de l'utilisateur
$stmt = $db->prepare("
    SELECT cm.*, u.nom as user_name, u.prenom as user_prenom, u.avatar as user_avatar 
    FROM contact_messages cm
    LEFT JOIN users u ON cm.user_id = u.id
    WHERE cm.id = ?
");
$stmt->execute([$message_id]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    redirect('list.php');
}

// Marquer comme lu si ce n'est pas déjà fait
if ($message['status'] === 'unread') {
    $update_stmt = $db->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
    $update_stmt->execute([$message_id]);
    $message['status'] = 'read';
}

// Variables pour le layout
$page_title = 'Détails du Message';
$page_icon = 'fas fa-envelope-open-text';
$current_page = 'messages/view.php';
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
                <!-- En-tête avec navigation -->
                <div class="admin-header">
                  
                    <div class="admin-header-actions">
                        <a href="reply.php?id=<?php echo $message_id; ?>" class="btn btn-admin-primary">
                            <i class="fas fa-reply"></i>
                            Répondre
                        </a>
                        <a href="list.php" class="btn btn-admin-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Retour
                        </a>
                    </div>
                </div>

                <div class="admin-grid">
                    <!-- Message original -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h2>
                                <i class="fas fa-envelope"></i>
                                Message Original
                            </h2>
                            <span class="badge badge-<?php echo $message['status']; ?>">
                                <?php 
                                switch($message['status']) {
                                    case 'replied': echo 'Répondu'; break;
                                    case 'read': echo 'Lu'; break;
                                    default: echo 'Non lu'; break;
                                }
                                ?>
                            </span>
                        </div>
                        
                        <div class="card-body">
                            <div class="message-details">
                                <div class="detail-row">
                                    <strong>De :</strong>
                                    <div class="user-info">
                                        <?php if (!empty($message['user_avatar'])): ?>
                                            <img src="../../uploads/avatars/<?php echo htmlspecialchars($message['user_avatar']); ?>" alt="Avatar" class="avatar">
                                        <?php else: ?>
                                            <div class="avatar-placeholder">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                        <span>
                                            <?php echo htmlspecialchars($message['user_name'] ?? $message['nom']); ?>
                                            <?php if (!empty($message['user_prenom'])): ?>
                                                <br><small><?php echo htmlspecialchars($message['user_prenom']); ?></small>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <strong>Email :</strong>
                                    <span><?php echo htmlspecialchars($message['email']); ?></span>
                                </div>
                                <?php if (!empty($message['telephone'])): ?>
                                    <div class="detail-row">
                                        <strong>Téléphone :</strong>
                                        <span><?php echo htmlspecialchars($message['telephone']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="detail-row">
                                    <strong>Sujet :</strong>
                                    <span><?php echo htmlspecialchars($message['sujet']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <strong>Date :</strong>
                                    <span><?php echo formatDateFrench($message['created_at']); ?></span>
                                </div>
                            </div>
                            
                            <div class="message-content">
                                <h3>Message :</h3>
                                <div class="message-text">
                                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Réponse de l'admin -->
                    <?php if (!empty($message['admin_response'])): ?>
                        <div class="admin-card">
                            <div class="card-header">
                                <h2>
                                    <i class="fas fa-reply"></i>
                                    Votre Réponse
                                </h2>
                                <span class="badge badge-replied">Répondu</span>
                            </div>
                            
                            <div class="card-body">
                                <div class="message-content">
                                    <div class="response-text">
                                        <?php echo nl2br(htmlspecialchars($message['admin_response'])); ?>
                                    </div>
                                </div>
                                <div class="message-meta">
                                    <div class="detail-row">
                                        <strong>Date de réponse :</strong>
                                        <span><?php echo formatDateFrench($message['updated_at']); ?></span>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <a href="reply.php?id=<?php echo $message_id; ?>" class="btn btn-admin-primary">
                                        <i class="fas fa-edit"></i>
                                        Modifier la réponse
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <?php includeAdminScripts(2); ?>
    
    <style>
        .admin-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .avatar, .avatar-placeholder {
            width: 48px;
            height: 48px;
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
        
        .message-details {
            margin-bottom: 1.5rem;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-row strong {
            min-width: 120px;
            color: var(--admin-dark);
        }
        
        .message-content h3 {
            color: var(--admin-dark);
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .message-text {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary-color);
            line-height: 1.6;
            color: var(--admin-dark);
        }
        
        .response-text {
            background: rgba(34, 197, 94, 0.05);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--admin-success);
            line-height: 1.6;
            color: var(--admin-dark);
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










        
    </style>
</body>
</html>