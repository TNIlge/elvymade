<?php
/**
 * Page de contact moderne - Réservée aux utilisateurs connectés
 * ElvyMade - Site de prospection de bijoux
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

// Traitement du formulaire de contact
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        // Récupérer et nettoyer les données
        $sujet = sanitizeInput($_POST['sujet']);
        $message = sanitizeInput($_POST['message']);
        
        // Validation des données
        if (empty($sujet) || empty($message)) {
            $error = 'Veuillez remplir tous les champs obligatoires.';
        } else {
            try {
                // Insérer le message dans la base de données
                $stmt = $db->prepare("
                    INSERT INTO contact_messages (nom, email, telephone, sujet, message, status, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, 'unread', ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $user['nom'],
                    $user['email'],
                    $user['telephone'],
                    $sujet,
                    $message,
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['HTTP_USER_AGENT']
                ]);
                
                $success = 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.';
                
                // Réinitialiser les variables pour vider le formulaire
                $sujet = $message = '';
                
            } catch (Exception $e) {
                $error = 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer.';
            }
        }
    }
}

// Générer un token CSRF
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Elvy.Made</title>
    <meta name="description" content="Contactez ElvyMade pour toute question sur nos bijoux. Notre équipe est à votre disposition.">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="logged-in">
    <?php include '../includes/header.php'; ?>
    
    <div class="container" style="padding: var(--spacing-12) var(--spacing-4);">
        <!-- Fil d'Ariane -->
        <nav style="margin-bottom: var(--spacing-8);">
            <a href="../index.php" style="color: var(--gray-600); text-decoration: none;">Accueil</a>
            <span style="margin: 0 var(--spacing-2); color: var(--gray-400);">/</span>
            <span style="color: var(--primary-color); font-weight: 600;">Contact</span>
        </nav>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-16); align-items: start;">
            <!-- Informations de contact -->
            <div>
                <h1 style="color: var(--primary-color); margin-bottom: var(--spacing-6); font-size: var(--font-size-3xl);">
                    <i class="fas fa-envelope"></i>
                    Contactez-nous
                </h1>
                
                <div style="margin-bottom: var(--spacing-8);">
                    <div style="display: flex; align-items: center; gap: var(--spacing-4); margin-bottom: var(--spacing-6);">
                        <div style="width: 60px; height: 60px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-phone" style="color: var(--white); font-size: var(--font-size-xl);"></i>
                        </div>
                        <div>
                            <h3 style="color: var(--gray-900); margin-bottom: var(--spacing-1);">Appelez-nous</h3>
                            <p style="color: var(--gray-600); margin: 0;">Nous sommes disponibles 7j/7j</p>
                        </div>
                    </div>
                    <p style="color: var(--gray-700); font-size: var(--font-size-lg); font-weight: 600;">
                        Téléphone: +237 6 96 09 58 05
                    </p>
                </div>

                <div style="margin-bottom: var(--spacing-8);">
                    <div style="display: flex; align-items: center; gap: var(--spacing-4); margin-bottom: var(--spacing-6);">
                        <div style="width: 60px; height: 60px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-envelope" style="color: var(--white); font-size: var(--font-size-xl);"></i>
                        </div>
                        <div>
                            <h3 style="color: var(--gray-900); margin-bottom: var(--spacing-1);">Écrivez-nous</h3>
                            <p style="color: var(--gray-600); margin: 0;">Remplissez notre formulaire et nous vous contacterons sous 24h</p>
                        </div>
                    </div>
                    <p style="color: var(--gray-700); margin-bottom: var(--spacing-2);">
                        Email: <?php echo SITE_EMAIL; ?>
                    </p>
                    <p style="color: var(--gray-700);">
                        Support: support@elvymade.com
                    </p>
                </div>

                <div>
                    <div style="display: flex; align-items: center; gap: var(--spacing-4); margin-bottom: var(--spacing-6);">
                        <div style="width: 60px; height: 60px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-map-marker-alt" style="color: var(--white); font-size: var(--font-size-xl);"></i>
                        </div>
                        <div>
                            <h3 style="color: var(--gray-900); margin-bottom: var(--spacing-1);">Notre Localisation</h3>
                            <p style="color: var(--gray-600); margin: 0;">Venez nous rendre visite</p>
                        </div>
                    </div>
                    <p style="color: var(--gray-700);">
                        Douala, Cameroun<br>
                        Quartier Bonanjo
                    </p>
                </div>
            </div>

            <!-- Formulaire de contact -->
            <div style="background: var(--white); padding: var(--spacing-8); border-radius: var(--border-radius-xl); box-shadow: var(--shadow-lg);">
                <h2 style="color: var(--primary-color); margin-bottom: var(--spacing-6); font-size: var(--font-size-2xl);">
                    Envoyez-nous un Message
                </h2>
                
                <div style="background: var(--gray-50); padding: var(--spacing-4); border-radius: var(--border-radius); margin-bottom: var(--spacing-6);">
                    <p style="margin: 0; color: var(--gray-700);">
                        <i class="fas fa-user" style="color: var(--primary-color); margin-right: var(--spacing-2);"></i>
                        Connecté en tant que: <strong><?php echo htmlspecialchars($user['nom']); ?></strong>
                    </p>
                    <p style="margin: 0; color: var(--gray-600); font-size: var(--font-size-sm);">
                        <i class="fas fa-envelope" style="color: var(--primary-color); margin-right: var(--spacing-2);"></i>
                        <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                </div>
                
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
                
                <form method="POST" action="contact.php" style="display: grid; gap: var(--spacing-4);">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div>
                        <label style="display: block; margin-bottom: var(--spacing-2); color: var(--gray-700); font-weight: 500;">
                            Sujet <span style="color: var(--error-color);">*</span>
                        </label>
                        <input type="text" name="sujet" required 
                               value="<?php echo isset($sujet) ? htmlspecialchars($sujet) : ''; ?>"
                               style="width: 100%; padding: var(--spacing-3); border: 2px solid var(--gray-200); border-radius: var(--border-radius); font-size: var(--font-size-base); transition: var(--transition);"
                               placeholder="Objet de votre message">
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: var(--spacing-2); color: var(--gray-700); font-weight: 500;">
                            Votre Message <span style="color: var(--error-color);">*</span>
                        </label>
                        <textarea name="message" required rows="6"
                                  style="width: 100%; padding: var(--spacing-3); border: 2px solid var(--gray-200); border-radius: var(--border-radius); font-size: var(--font-size-base); transition: var(--transition); resize: vertical;"
                                  placeholder="Décrivez votre demande..."><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg" style="justify-self: start;">
                        <i class="fas fa-paper-plane"></i>
                        Envoyer le Message
                    </button>
                </form>
                
                <div style="margin-top: var(--spacing-6); padding-top: var(--spacing-4); border-top: 1px solid var(--gray-200); text-align:center;">
    <p style="color: var(--gray-600); font-size: var(--font-size-sm); margin: 0;">
        <i class="fas fa-info-circle" style="color: var(--primary-color); margin-right: var(--spacing-2);"></i>
        Vous pouvez également consulter vos messages et nos réponses dans votre
    </p>
    <a href="messages.php" class="btn-espace-personnel">
        <i class="fas fa-user-circle"></i>
        Espace personnel
    </a>
</div>
            </div>
        </div>
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
        // Améliorer l'UX des champs de formulaire
        document.querySelectorAll('input, textarea').forEach(field => {
            field.addEventListener('focus', function() {
                this.style.borderColor = 'var(--primary-color)';
                this.style.boxShadow = '0 0 0 3px rgba(233, 30, 99, 0.1)';
            });
            
            field.addEventListener('blur', function() {
                this.style.borderColor = 'var(--gray-200)';
                this.style.boxShadow = 'none';
            });
        });
    </script>
    
    <style>
        @media (max-width: 768px) {
            .container > div {
                grid-template-columns: 1fr !important;
                gap: var(--spacing-8) !important;
            }
        }
         /* Styles spécifiques pour favorites.php - Responsive */
    @media (max-width: 768px) {
        .favorites-grid {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)) !important;
        }

        .favorite-item {
            height: auto;
            min-height: 420px; /* Hauteur fixe */
            display: flex;
            flex-direction: column;
        }

        .product-image {
            height: 180px !important; /* Hauteur fixe */
            flex-shrink: 0;
        }

        .favorite-item > div:not(.product-image) {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .favorite-item .product-actions {
            margin-top: auto;
        }
    }

    @media (max-width: 480px) {
        .favorites-grid {
            grid-template-columns: 1fr !important;
        }

        .favorite-item {
            min-height: 400px;
        }

        .product-image {
            height: 160px !important;
        }

        .product-actions {
            flex-direction: column;
        }
    }

        
    .btn-espace-personnel {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1.2rem;
        padding: 0.7rem 1.5rem;
        background: var(--primary-color, #e91e63);
        color: #fff !important;
        border: none;
        border-radius: var(--border-radius, 8px);
        font-size: 1.05rem;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(233,30,99,0.08);
        text-decoration: none;
        transition: background 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .btn-espace-personnel:hover,
    .btn-espace-personnel:focus {
        background:rgb(120, 24, 194);
        color: #fff !important;
        box-shadow: 0 4px 16px rgba(233,30,99,0.13);
        text-decoration: none;
    }
    </style>
</body>
</html>
