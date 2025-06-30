<?php
/**
 * Répondre à un message de contact - Administration
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

// Traitement du formulaire de réponse
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $admin_response = sanitizeInput($_POST['admin_response']);
        
        if (empty($admin_response)) {
            $error = 'Veuillez saisir une réponse.';
        } else {
            try {
                $stmt = $db->prepare("
                    UPDATE contact_messages 
                    SET admin_response = ?, status = 'replied', replied_at = NOW(), replied_by = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$admin_response, $_SESSION['user_id'], $message_id])) {
                    $success = 'Réponse envoyée avec succès !';
                    
                    // Rafraîchir les données du message
                    $stmt = $db->prepare("
                        SELECT cm.*, u.nom as user_name, u.prenom as user_prenom, u.avatar as user_avatar 
                        FROM contact_messages cm
                        LEFT JOIN users u ON cm.user_id = u.id
                        WHERE cm.id = ?
                    ");
                    $stmt->execute([$message_id]);
                    $message = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Envoyer une notification à l'utilisateur
                    if ($message['user_id']) {
                        $notification_title = "Réponse à votre message";
                        $notification_message = "Vous avez reçu une réponse à votre message : " . substr($message['sujet'], 0, 50) . "...";
                        
                        $notif_stmt = $db->prepare("
                            INSERT INTO notifications 
                            (user_id, type, title, message, related_id, created_at) 
                            VALUES (?, 'message_reply', ?, ?, ?, NOW())
                        ");
                        $notif_stmt->execute([
                            $message['user_id'],
                            $notification_title,
                            $notification_message,
                            $message_id
                        ]);
                    }
                } else {
                    $error = 'Erreur lors de l\'envoi de la réponse.';
                }
            } catch (Exception $e) {
                $error = 'Une erreur est survenue lors de l\'envoi de la réponse.';
            }
        }
    }
}

// Générer un token CSRF
$csrf_token = generateCSRFToken();

// Variables pour le layout
$page_title = 'Répondre au Message';
$page_icon = 'fas fa-reply';
$current_page = 'messages/reply.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Administration ElvyMade</title>
    <?php includeAdminAssets(2); ?>
       <link rel="stylesheet" href="style.css">

        <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
</head>
<body>
    <div class="admin-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include '../includes/header.php'; ?>
            
            <div class="admin-content">
                
  <div class="admin-header-actions">
                        <a href="list.php" class="btn btn-admin-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Retour à la liste
                        </a>
                    </div>
                <!-- Messages d'alerte -->
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

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

                    <!-- Formulaire de réponse -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h2>
                                <i class="fas fa-reply"></i>
                                <?php echo !empty($message['admin_response']) ? 'Modifier la Réponse' : 'Votre Réponse'; ?>
                            </h2>
                        </div>
                        
                        <div class="card-body">
                            <?php if (!empty($message['admin_response'])): ?>
                                <div class="current-response">
                                    <h3>Réponse actuelle :</h3>
                                    <div class="response-text">
                                        <?php echo nl2br(htmlspecialchars($message['admin_response'])); ?>
                                    </div>
                                </div>
                                <hr>
                            <?php endif; ?>
                            
                            <form method="POST" action="reply.php?id=<?php echo $message_id; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                
                                <div class="form-group">
                                    <label for="admin_response" class="form-label">
                                        <?php echo !empty($message['admin_response']) ? 'Nouvelle réponse :' : 'Votre réponse :'; ?>
                                        <span class="required">*</span>
                                    </label>
                                    <textarea id="admin_response" name="admin_response" class="form-textarea" 
                                              rows="8" required placeholder="Saisissez votre réponse ici..."><?php echo !empty($message['admin_response']) ? htmlspecialchars($message['admin_response']) : ''; ?></textarea>
                                    <small class="form-help">
                                        Cette réponse sera visible par l'utilisateur dans son espace personnel.
                                    </small>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-admin-primary">
                                        <i class="fas fa-paper-plane"></i>
                                        <?php echo !empty($message['admin_response']) ? 'Modifier la Réponse' : 'Envoyer la Réponse'; ?>
                                    </button>
                                    <a href="view.php?id=<?php echo $message_id; ?>" class="btn btn-admin-secondary">
                                        <i class="fas fa-times"></i>
                                        Annuler
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <?php includeAdminScripts(2); ?>
    
    <style>
        .admin-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            align-items: start;
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
        
        .current-response {
            margin-bottom: 1.5rem;
        }
        
        .current-response h3 {
            color: var(--admin-success);
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .response-text {
            background: rgba(34, 197, 94, 0.05);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--admin-success);
            line-height: 1.6;
            color: var(--admin-dark);
        }
        
        .form-textarea {
            min-height: 200px;
            resize: vertical;
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
        
        @media (max-width: 1024px) {
            .admin-grid {
                grid-template-columns: 1fr;
            }
        }









        
    </style>
</body>
</html>