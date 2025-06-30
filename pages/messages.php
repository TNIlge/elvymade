<?php
/**
 * Page des messages utilisateur
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

// Récupérer les informations de l'utilisateur connecté
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Traitement de la réponse à un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    $message_id = intval($_POST['message_id']);
    $reply_content = trim($_POST['reply_content']);
    
    if (!empty($reply_content)) {
        // Vérifier que le message appartient bien à l'utilisateur
        $check_stmt = $db->prepare("SELECT id FROM contact_messages WHERE id = ? AND email = ?");
        $check_stmt->execute([$message_id, $user['email']]);
        
        if ($check_stmt->fetch()) {
            $update_stmt = $db->prepare("UPDATE contact_messages SET user_reply = ?, status = 'replied' WHERE id = ?");
            $update_stmt->execute([$reply_content, $message_id]);
            $_SESSION['success_message'] = "Votre réponse a bien été envoyée.";
            header("Location: messages.php");
            exit();
        }
    }
}

// Traitement de la suppression d'un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message'])) {
    $message_id = intval($_POST['message_id']);
    
    // Vérifier que le message appartient bien à l'utilisateur
    $check_stmt = $db->prepare("SELECT id FROM contact_messages WHERE id = ? AND email = ?");
    $check_stmt->execute([$message_id, $user['email']]);
    
    if ($check_stmt->fetch()) {
        $delete_stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ?");
        $delete_stmt->execute([$message_id]);
        $_SESSION['success_message'] = "Le message a bien été supprimé.";
        header("Location: messages.php");
        exit();
    }
}

// Paramètres de pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Récupérer le nombre total de messages (reçus et envoyés)
$count_stmt = $db->prepare("SELECT COUNT(*) as total FROM contact_messages WHERE (email = ? AND is_from_user = 0) OR (user_id = ? AND is_from_user = 1)");
$count_stmt->execute([$user['email'], $_SESSION['user_id']]);
$total_messages = $count_stmt->fetchColumn();
// Récupérer les messages de l'utilisateur (reçus et envoyés)
$stmt = $db->prepare("
    SELECT *, 
           CASE 
               WHEN is_from_user = 1 THEN 'sent'
               ELSE 'received'
           END as message_type
    FROM contact_messages 
    WHERE (email = ? AND is_from_user = 0) OR (user_id = ? AND is_from_user = 1)
    ORDER BY created_at DESC 
    LIMIT $per_page OFFSET $offset
");
$stmt->execute([$user['email'], $_SESSION['user_id']]);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Marquer les messages reçus non lus comme lus
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Messages - Elvy.Made</title>
    <meta name="description" content="Consultez vos messages et les réponses de l'équipe ElvyMade.">
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
            <span style="color: var(--primary-color); font-weight: 600;">Mes Messages</span>
        </nav>

        <!-- En-tête des messages -->
        <div style="text-align: center; margin-bottom: var(--spacing-8);">
            <h1 style="font-size: var(--font-size-4xl); color: var(--primary-color); margin-bottom: var(--spacing-3); display: flex; align-items: center; justify-content: center; gap: var(--spacing-3);">
                <i class="fas fa-envelope"></i>
                Mes Messages
            </h1>
            <p style="font-size: var(--font-size-lg); color: var(--gray-600);">
                <?php if ($total_messages > 0): ?>
                    Vous avez <?php echo $total_messages; ?> message<?php echo $total_messages > 1 ? 's' : ''; ?>
                <?php else: ?>
                    Vous n'avez pas encore de messages
                <?php endif; ?>
            </p>
        </div>
        
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success" style="margin-bottom: var(--spacing-6);">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($messages)): ?>
            <!-- Aucun message -->
            <div style="text-align: center; padding: var(--spacing-12) 0; background: var(--gray-50); border-radius: var(--border-radius-xl); margin: var(--spacing-6) 0;">
                <div style="font-size: var(--font-size-5xl); color: var(--primary-light); margin-bottom: var(--spacing-4);">
                    <i class="fas fa-envelope-open"></i>
                </div>
                <h3 style="font-size: var(--font-size-2xl); color: var(--gray-800); margin-bottom: var(--spacing-2);">
                    Aucun message pour le moment
                </h3>
                <p style="color: var(--gray-600); margin-bottom: var(--spacing-6); max-width: 500px; margin-left: auto; margin-right: auto;">
                    Vous n'avez pas encore envoyé de messages. Contactez-nous pour toute question ou demande d'information.
                </p>
                <div style="display: flex; gap: var(--spacing-4); justify-content: center;">
                    <a href="contact.php" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Envoyer un message
                    </a>
                    <a href="../index.php" class="btn btn-outline">
                        <i class="fas fa-home"></i>
                        Retour à l'accueil
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Liste des messages -->
            <div style="display: grid; gap: var(--spacing-6);">
                <?php foreach ($messages as $message): ?>
                    <div style="background: var(--white); border-radius: var(--border-radius-lg); box-shadow: var(--shadow); overflow: hidden; border-left: 4px solid <?php echo $message['message_type'] === 'sent' ? 'var(--secondary-color)' : 'var(--primary-color)'; ?>;">
                        <!-- En-tête du message -->
                        <div style="padding: var(--spacing-6); border-bottom: 1px solid var(--gray-200);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--spacing-4);">
                                <div>
                                    <h3 style="font-size: var(--font-size-xl); color: var(--gray-900); margin-bottom: var(--spacing-2);">
                                        <?php echo htmlspecialchars($message['sujet']); ?>
                                        <?php if ($message['message_type'] === 'sent'): ?>
                                            <span class="badge badge-secondary" style="margin-left: var(--spacing-2);">Envoyé</span>
                                        <?php else: ?>
                                            <span class="badge badge-primary" style="margin-left: var(--spacing-2);">Reçu</span>
                                        <?php endif; ?>
                                    </h3>
                                    <div style="display: flex; align-items: center; gap: var(--spacing-4); color: var(--gray-600); font-size: var(--font-size-sm);">
                                        <span>
                                            <i class="fas fa-calendar-alt"></i>
                                            <?php echo formatDateFrench($message['created_at'], 'd/m/Y à H:i'); ?>
                                        </span>
                                        <span class="badge <?php echo $message['status'] === 'replied' ? 'badge-success' : ($message['status'] === 'read' ? 'badge-warning' : 'badge-danger'); ?>">
                                            <?php 
                                            switch($message['status']) {
                                                case 'replied': echo 'Répondu'; break;
                                                case 'read': echo 'Lu'; break;
                                                default: echo 'Non lu'; break;
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Voulez-vous vraiment supprimer ce message ?');">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <button type="submit" name="delete_message" class="btn-action" title="Supprimer">
                                            <i class="fas fa-trash" style="color: var(--red-500);"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <?php if ($message['message_type'] === 'received'): ?>
                                <!-- Message original (pour les messages reçus) -->
                                <div style="background: var(--gray-50); padding: var(--spacing-4); border-radius: var(--border-radius); margin-bottom: var(--spacing-4);">
                                    <h4 style="color: var(--gray-700); margin-bottom: var(--spacing-2); font-size: var(--font-size-sm); text-transform: uppercase; letter-spacing: 0.05em;">
                                        <i class="fas fa-user"></i>
                                        Votre message
                                    </h4>
                                    <p style="color: var(--gray-800); line-height: 1.6; margin: 0;">
                                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                    </p>
                                </div>
                                
                                <!-- Réponse de l'admin -->
                                <?php if (!empty($message['admin_response'])): ?>
                                    <div style="background: rgba(var(--primary-color-rgb), 0.05); padding: var(--spacing-4); border-radius: var(--border-radius); border-left: 3px solid var(--primary-color); margin-bottom: var(--spacing-4);">
                                        <h4 style="color: var(--primary-color); margin-bottom: var(--spacing-2); font-size: var(--font-size-sm); text-transform: uppercase; letter-spacing: 0.05em;">
                                            <i class="fas fa-reply"></i>
                                            Réponse de l'équipe ElvyMade
                                        </h4>
                                        <p style="color: var(--gray-800); line-height: 1.6; margin: 0;">
                                            <?php echo nl2br(htmlspecialchars($message['admin_response'])); ?>
                                        </p>
                                    </div>
                                    
                                    <!-- Formulaire de réponse -->
                                    <form method="POST">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <div style="margin-bottom: var(--spacing-4);">
                                            <label for="reply_content_<?php echo $message['id']; ?>" style="display: block; margin-bottom: var(--spacing-2); color: var(--gray-700); font-weight: 500;">
                                                <i class="fas fa-reply"></i> Répondre à ce message
                                            </label>
                                            <textarea id="reply_content_<?php echo $message['id']; ?>" name="reply_content" rows="3" style="width: 100%; padding: var(--spacing-3); border: 1px solid var(--gray-300); border-radius: var(--border-radius);" required></textarea>
                                        </div>
                                        <button type="submit" name="reply_message" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i> Envoyer la réponse
                                        </button>
                                    </form>
                                <?php elseif (empty($message['admin_response'])): ?>
                                    <div style="background: var(--gray-100); padding: var(--spacing-3); border-radius: var(--border-radius); text-align: center;">
                                        <p style="color: var(--gray-600); margin: 0; font-style: italic;">
                                            <i class="fas fa-clock"></i>
                                            En attente de réponse...
                                        </p>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <!-- Contenu des messages envoyés -->
                                <div style="background: rgba(var(--secondary-color-rgb), 0.05); padding: var(--spacing-4); border-radius: var(--border-radius); border-left: 3px solid var(--secondary-color);">
                                    <h4 style="color: var(--secondary-color); margin-bottom: var(--spacing-2); font-size: var(--font-size-sm); text-transform: uppercase; letter-spacing: 0.05em;">
                                        <i class="fas fa-paper-plane"></i>
                                        Message envoyé
                                    </h4>
                                    <p style="color: var(--gray-800); line-height: 1.6; margin: 0;">
                                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                    </p>
                                </div>
                                
                                <!-- Réponse de l'utilisateur (si message envoyé par admin et répond par user) -->
                                <?php if (!empty($message['user_reply'])): ?>
                                    <div style="background: var(--gray-50); padding: var(--spacing-4); border-radius: var(--border-radius); margin-top: var(--spacing-4);">
                                        <h4 style="color: var(--gray-700); margin-bottom: var(--spacing-2); font-size: var(--font-size-sm); text-transform: uppercase; letter-spacing: 0.05em;">
                                            <i class="fas fa-reply"></i>
                                            Votre réponse
                                        </h4>
                                        <p style="color: var(--gray-800); line-height: 1.6; margin: 0;">
                                            <?php echo nl2br(htmlspecialchars($message['user_reply'])); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination" style="margin-top: var(--spacing-8);">
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
                               class="pagination-number <?php echo $i === $page ? 'active' : ''; ?>">
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
            
            <!-- Actions rapides -->
            <div style="margin-top: var(--spacing-8); text-align: center;">
                <a href="contact.php" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i>
                    Envoyer un nouveau message
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <style>
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: var(--border-radius);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-block;
        }

        .badge-primary {
            background: var(--primary-light);
            color: var(--primary-dark);
        }
        
        .badge-secondary {
            background: var(--secondary-light);
            color: var(--secondary-dark);
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
        
        .btn-action {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-action:hover {
            background: var(--gray-100);
        }
        
        .alert-success {
            background: #dcfce7;
            color: #166534;
            padding: var(--spacing-3) var(--spacing-4);
            border-radius: var(--border-radius);
            border-left: 4px solid #22c55e;
        }
        
        @media (max-width: 768px) {
            .container > div:first-child {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</body>
</html>