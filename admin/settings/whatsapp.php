<?php
/**
 * Configuration WhatsApp - Administration
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

// Récupérer les paramètres WhatsApp actuels
$whatsapp_settings = [];
$stmt = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'whatsapp_%'");
while ($row = $stmt->fetch()) {
    $whatsapp_settings[$row['setting_key']] = $row['setting_value'];
}

// Valeurs par défaut
$default_whatsapp_settings = [
    'whatsapp_number' => '+237123456789',
    'whatsapp_enabled' => '1',
    'whatsapp_general_message' => 'Bonjour ! Je suis intéressé par vos produits sur ElvyMade.',
    'whatsapp_product_message' => 'Bonjour ! Je suis intéressé par ce produit : ',
    'whatsapp_category_message' => 'Bonjour ! Je cherche des produits dans la catégorie : '
];

foreach ($default_whatsapp_settings as $key => $value) {
    if (!isset($whatsapp_settings[$key])) {
        $whatsapp_settings[$key] = $value;
    }
}

// Traitement du formulaire
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        // Récupérer et nettoyer les données
        $new_whatsapp_settings = [
            'whatsapp_number' => sanitizeInput($_POST['whatsapp_number']),
            'whatsapp_enabled' => isset($_POST['whatsapp_enabled']) ? '1' : '0',
            'whatsapp_general_message' => sanitizeInput($_POST['whatsapp_general_message']),
            'whatsapp_product_message' => sanitizeInput($_POST['whatsapp_product_message']),
            'whatsapp_category_message' => sanitizeInput($_POST['whatsapp_category_message'])
        ];
        
        // Validation
        if (empty($new_whatsapp_settings['whatsapp_number'])) {
            $error = 'Le numéro WhatsApp est obligatoire.';
        } elseif (!preg_match('/^\+?[1-9]\d{1,14}$/', $new_whatsapp_settings['whatsapp_number'])) {
            $error = 'Format de numéro WhatsApp invalide. Utilisez le format international (+237XXXXXXXXX).';
        } else {
            // Mettre à jour les paramètres WhatsApp
            $success_count = 0;
            
            foreach ($new_whatsapp_settings as $key => $value) {
                // Vérifier si le paramètre existe
                $stmt = $db->prepare("SELECT id FROM settings WHERE setting_key = ?");
                $stmt->execute([$key]);
                
                if ($stmt->rowCount() > 0) {
                    // Mettre à jour
                    $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                    if ($stmt->execute([$value, $key])) {
                        $success_count++;
                    }
                } else {
                    // Insérer
                    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                    if ($stmt->execute([$key, $value])) {
                        $success_count++;
                    }
                }
            }
            
            if ($success_count === count($new_whatsapp_settings)) {
                $success = 'Configuration WhatsApp mise à jour avec succès.';
                $whatsapp_settings = $new_whatsapp_settings; // Mettre à jour les valeurs affichées
            } else {
                $error = 'Erreur lors de la mise à jour de certains paramètres WhatsApp.';
            }
        }
    }
}

// Variables pour le layout admin
$page_title = 'Configuration WhatsApp';
$page_icon = 'fab fa-whatsapp';
$current_page = 'settings/whatsapp';
$unread_messages = getUnreadMessagesCount($db);

// Générer un token CSRF
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Administration ElvyMade</title>
    <?php includeAdminAssets(2); ?>
</head>
<body>
    <div class="admin-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include '../includes/header.php'; ?>
            
            <div class="admin-content">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <span class="alert-message"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <span class="alert-message"><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="settings-container">
                    <div class="whatsapp-info">
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div class="info-content">
                                <h3>Configuration WhatsApp</h3>
                                <p>Configurez l'intégration WhatsApp pour permettre aux clients de vous contacter directement depuis votre site.</p>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="whatsapp.php" class="admin-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <!-- Configuration de base -->
                        <div class="settings-section">
                            <h3>Configuration de base</h3>
                            
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="whatsapp_enabled" <?php echo $whatsapp_settings['whatsapp_enabled'] === '1' ? 'checked' : ''; ?>>
                                    <span class="checkmark"></span>
                                    Activer l'intégration WhatsApp
                                </label>
                                <small>Afficher les boutons WhatsApp sur le site</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="whatsapp_number" class="form-label">Numéro WhatsApp <span class="required">*</span></label>
                                <input type="tel" id="whatsapp_number" name="whatsapp_number" class="form-input" required 
                                       value="<?php echo htmlspecialchars($whatsapp_settings['whatsapp_number']); ?>"
                                       placeholder="+237123456789">
                                <small>Format international sans espaces (ex: +237123456789)</small>
                            </div>
                        </div>
                        
                        <!-- Messages prédéfinis -->
                        <div class="settings-section">
                            <h3>Messages prédéfinis</h3>
                            
                            <div class="form-group">
                                <label for="whatsapp_general_message" class="form-label">Message général</label>
                                <textarea id="whatsapp_general_message" name="whatsapp_general_message" class="form-textarea" rows="3"><?php echo htmlspecialchars($whatsapp_settings['whatsapp_general_message']); ?></textarea>
                                <small>Message envoyé lors d'un contact général</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="whatsapp_product_message" class="form-label">Message pour un produit</label>
                                <textarea id="whatsapp_product_message" name="whatsapp_product_message" class="form-textarea" rows="3"><?php echo htmlspecialchars($whatsapp_settings['whatsapp_product_message']); ?></textarea>
                                <small>Message envoyé lors d'une demande sur un produit spécifique (le nom du produit sera ajouté automatiquement)</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="whatsapp_category_message" class="form-label">Message pour une catégorie</label>
                                <textarea id="whatsapp_category_message" name="whatsapp_category_message" class="form-textarea" rows="3"><?php echo htmlspecialchars($whatsapp_settings['whatsapp_category_message']); ?></textarea>
                                <small>Message envoyé lors d'une demande sur une catégorie (le nom de la catégorie sera ajouté automatiquement)</small>
                            </div>
                        </div>
                        
                        <!-- Test -->
                        <div class="settings-section">
                            <h3>Test de configuration</h3>
                            <div class="test-whatsapp">
                                <p>Testez votre configuration WhatsApp :</p>
                                <button type="button" onclick="testWhatsApp()" class="btn btn-admin-success">
                                    <i class="fab fa-whatsapp"></i>
                                    Tester WhatsApp
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-admin-primary">
                                <i class="fas fa-save"></i>
                                Enregistrer la configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <?php includeAdminScripts(2); ?>
    <script>
        function testWhatsApp() {
            const number = document.getElementById('whatsapp_number').value;
            const message = document.getElementById('whatsapp_general_message').value;
            
            if (!number) {
                alert('Veuillez d\'abord saisir un numéro WhatsApp');
                return;
            }
            
            // Nettoyer le numéro (supprimer les espaces et le +)
            const cleanNumber = number.replace(/\s+/g, '').replace(/^\+/, '');
            
            // Construire l'URL WhatsApp
            const whatsappUrl = `https://wa.me/${cleanNumber}?text=${encodeURIComponent(message)}`;
            
            // Ouvrir WhatsApp
            window.open(whatsappUrl, '_blank');
        }
    </script>
</body>
</html>
