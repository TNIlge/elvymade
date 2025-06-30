<?php
/**
 * Envoyer un message à un utilisateur - Administration
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

// Connexion à la base de données
$db = getDBConnection();

// Récupérer la liste des utilisateurs
$users_stmt = $db->query("SELECT id, nom, prenom, email FROM users WHERE id != " . $_SESSION['user_id'] . " ORDER BY nom");
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire
$error = '';
$success = '';
$selected_user = '';
$subject = '';
$message_content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $selected_user = sanitizeInput($_POST['user_id']);
        $subject = sanitizeInput($_POST['subject']);
        $message_content = sanitizeInput($_POST['message']);
        
        // Validation
        if (empty($selected_user)) {
            $error = 'Veuillez sélectionner un utilisateur.';
        } elseif (empty($subject)) {
            $error = 'Veuillez saisir un sujet.';
        } elseif (empty($message_content)) {
            $error = 'Veuillez saisir un message.';
        } else {
            // Récupérer les infos de l'utilisateur
            $user_stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ?");
            $user_stmt->execute([$selected_user]);
            $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                try {
                    // Enregistrer le message
                    $stmt = $db->prepare("
                        INSERT INTO contact_messages 
                        (user_id, nom, email, sujet, message, status, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, 'read', NOW(), NOW())
                    ");
                    
                    $stmt->execute([
                        $selected_user,
                        'Admin ElvyMade',
                        'admin@elvymade.cm',
                        $subject,
                        $message_content
                    ]);
                    
                    $success = 'Message envoyé avec succès à ' . htmlspecialchars($user['nom']);
                } catch (Exception $e) {
                    $error = 'Une erreur est survenue lors de l\'envoi du message.';
                }
            } else {
                $error = 'Utilisateur non trouvé.';
            }
        }
    }
}

// Générer un token CSRF
$csrf_token = generateCSRFToken();

// Variables pour le layout
$page_title = 'Envoyer un Message';
$page_icon = 'fas fa-paper-plane';
$current_page = 'messages/send.php';
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

                <div class="admin-card">
                    <div class="card-header">
                        <h2>
                            <i class="fas fa-user"></i>
                            Nouveau message
                        </h2>
                    </div>
                    
                    <div class="card-body">
                        <form method="POST" action="send.php">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="form-group">
                                <label for="user_id" class="form-label">
                                    Destinataire
                                    <span class="required">*</span>
                                </label>
                                <select id="user_id" name="user_id" class="form-select" required>
                                    <option value="">Sélectionnez un utilisateur</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>" <?php echo $selected_user == $user['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['nom'] . ($user['prenom'] ? ' ' . $user['prenom'] : '') . ' (' . $user['email'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject" class="form-label">
                                    Sujet
                                    <span class="required">*</span>
                                </label>
                                <input type="text" id="subject" name="subject" class="form-input" 
                                       value="<?php echo htmlspecialchars($subject); ?>" required placeholder="Saisissez le sujet du message">
                            </div>
                            
                            <div class="form-group">
                                <label for="message" class="form-label">
                                    Message
                                    <span class="required">*</span>
                                </label>
                                <textarea id="message" name="message" class="form-textarea" 
                                          rows="8" required placeholder="Saisissez votre message ici..."><?php echo htmlspecialchars($message_content); ?></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-admin-primary">
                                    <i class="fas fa-paper-plane"></i>
                                    Envoyer le message
                                </button>
                                <button type="reset" class="btn btn-admin-secondary">
                                    <i class="fas fa-eraser"></i>
                                    Effacer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <?php includeAdminScripts(2); ?>
    
    <style>
        .form-textarea {
            min-height: 200px;
            resize: vertical;
        }





        
    </style>
</body>
</html>