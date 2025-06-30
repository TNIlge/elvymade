<?php
/**
 * Paramètres généraux - Administration
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

// Récupérer les paramètres actuels
$settings = [];
$stmt = $db->query("SELECT * FROM settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Valeurs par défaut si les paramètres n'existent pas
$default_settings = [
    'site_name' => 'ElvyMade',
    'site_description' => 'Marketplace Camerounaise - Prospection d\'articles en ligne',
    'site_email' => 'contact@elvymade.cm',
    'site_phone' => '+237 123 456 789',
    'site_address' => 'Douala, Cameroun',
    'items_per_page' => '12',
    'max_upload_size' => '5',
    'maintenance_mode' => '0'
];

foreach ($default_settings as $key => $value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $value;
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
        $new_settings = [
            'site_name' => sanitizeInput($_POST['site_name']),
            'site_description' => sanitizeInput($_POST['site_description']),
            'site_email' => sanitizeInput($_POST['site_email']),
            'site_phone' => sanitizeInput($_POST['site_phone']),
            'site_address' => sanitizeInput($_POST['site_address']),
            'items_per_page' => (int)$_POST['items_per_page'],
            'max_upload_size' => (int)$_POST['max_upload_size'],
            'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0'
        ];
        
        // Validation
        if (empty($new_settings['site_name']) || empty($new_settings['site_email'])) {
            $error = 'Le nom du site et l\'email sont obligatoires.';
        } elseif (!validateEmail($new_settings['site_email'])) {
            $error = 'Adresse email invalide.';
        } elseif ($new_settings['items_per_page'] < 1 || $new_settings['items_per_page'] > 100) {
            $error = 'Le nombre d\'éléments par page doit être entre 1 et 100.';
        } elseif ($new_settings['max_upload_size'] < 1 || $new_settings['max_upload_size'] > 50) {
            $error = 'La taille maximale d\'upload doit être entre 1 et 50 MB.';
        } else {
            // Mettre à jour les paramètres
            $success_count = 0;
            
            foreach ($new_settings as $key => $value) {
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
            
            if ($success_count === count($new_settings)) {
                $success = 'Paramètres mis à jour avec succès.';
                $settings = $new_settings; // Mettre à jour les valeurs affichées
            } else {
                $error = 'Erreur lors de la mise à jour de certains paramètres.';
            }
        }
    }
}

// Variables pour le layout admin
$page_title = 'Paramètres Généraux';
$page_icon = 'fas fa-cog';
$current_page = 'settings/general';
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
                    <form method="POST" action="general.php" class="admin-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <!-- Informations du site -->
                        <div class="settings-section">
                            <h3>Informations du site</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="site_name" class="form-label">Nom du site <span class="required">*</span></label>
                                    <input type="text" id="site_name" name="site_name" class="form-input" required 
                                           value="<?php echo htmlspecialchars($settings['site_name']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="site_email" class="form-label">Email du site <span class="required">*</span></label>
                                    <input type="email" id="site_email" name="site_email" class="form-input" required 
                                           value="<?php echo htmlspecialchars($settings['site_email']); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="site_description" class="form-label">Description du site</label>
                                <textarea id="site_description" name="site_description" class="form-textarea" rows="3"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="site_phone" class="form-label">Téléphone</label>
                                    <input type="tel" id="site_phone" name="site_phone" class="form-input" 
                                           value="<?php echo htmlspecialchars($settings['site_phone']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="site_address" class="form-label">Adresse</label>
                                    <input type="text" id="site_address" name="site_address" class="form-input" 
                                           value="<?php echo htmlspecialchars($settings['site_address']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Paramètres techniques -->
                        <div class="settings-section">
                            <h3>Paramètres techniques</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="items_per_page" class="form-label">Éléments par page</label>
                                    <input type="number" id="items_per_page" name="items_per_page" class="form-input" 
                                           min="1" max="100" value="<?php echo $settings['items_per_page']; ?>">
                                    <small>Nombre de produits affichés par page (1-100)</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="max_upload_size" class="form-label">Taille max upload (MB)</label>
                                    <input type="number" id="max_upload_size" name="max_upload_size" class="form-input" 
                                           min="1" max="50" value="<?php echo $settings['max_upload_size']; ?>">
                                    <small>Taille maximale des fichiers uploadés (1-50 MB)</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Maintenance -->
                        <div class="settings-section">
                            <h3>Maintenance</h3>
                            
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="maintenance_mode" <?php echo $settings['maintenance_mode'] === '1' ? 'checked' : ''; ?>>
                                    <span class="checkmark"></span>
                                    Mode maintenance
                                </label>
                                <small>Activer le mode maintenance pour empêcher l'accès au site public</small>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-admin-primary">
                                <i class="fas fa-save"></i>
                                Enregistrer les paramètres
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <?php includeAdminScripts(2); ?>
</body>
</html>
