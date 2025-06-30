<?php
/**
 * Ajouter une catégorie - Administration
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

// Traitement du formulaire
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        // Récupérer et nettoyer les données
        $nom = sanitizeInput($_POST['nom']);
        $description = sanitizeInput($_POST['description']);
        $image_filename = '';
        
        // Validation
        if (empty($nom)) {
            $error = 'Le nom de la catégorie est obligatoire.';
        } else {
            // Vérifier si la catégorie existe déjà
            $stmt = $db->prepare("SELECT id FROM categories WHERE nom = ?");
            $stmt->execute([$nom]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Une catégorie avec ce nom existe déjà.';
            } else {
                // Traitement de l'image si elle est fournie
                if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $upload_dir = '../../uploads/categories/';
                    $upload_result = uploadImage($_FILES['image'], $upload_dir);
                    
                    if ($upload_result['success']) {
                        $image_filename = $upload_result['filename'];
                    } else {
                        $error = $upload_result['message'];
                    }
                }
                
                if (empty($error)) {
                    // Insérer la catégorie
                    $stmt = $db->prepare("INSERT INTO categories (nom, description, image, created_at) VALUES (?, ?, ?, NOW())");
                    
                    if ($stmt->execute([$nom, $description, $image_filename])) {
                        $success = 'Catégorie ajoutée avec succès.';
                        // Réinitialiser les variables
                        $nom = $description = '';
                    } else {
                        $error = 'Erreur lors de l\'ajout de la catégorie.';
                        
                        // Supprimer l'image si elle a été uploadée
                        if (!empty($image_filename)) {
                            if (file_exists($upload_dir . $image_filename)) {
                                unlink($upload_dir . $image_filename);
                            }
                        }
                    }
                }
            }
        }
    }
}

// Messages non lus pour le sidebar
$unread_messages = 0;
try {
    $stmt = $db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'");
    $unread_messages = $stmt->fetchColumn();
} catch (Exception $e) {
    // Ignorer l'erreur si la table n'existe pas
}

// Générer un token CSRF
$csrf_token = generateCSRFToken();

// Variables pour le layout
$page_title = 'Ajouter une Catégorie';
$page_icon = 'fas fa-plus';
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
                
                <div class="admin-form">
                    <form method="POST" action="add.php" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="form-group">
                            <label for="nom" class="form-label">Nom de la catégorie <span class="required">*</span></label>
                            <input type="text" id="nom" name="nom" class="form-input" required 
                                   value="<?php echo isset($nom) ? htmlspecialchars($nom) : ''; ?>"
                                   placeholder="Ex: Électronique, Mode, Maison...">
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-textarea" rows="4"
                                      placeholder="Description de la catégorie (optionnel)"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="image" class="form-label">Image de la catégorie</label>
                            <div class="file-input-wrapper">
                                <input type="file" id="image" name="image" class="form-input-file" accept="image/*">
                                <label for="image" class="file-input-label">
                                    <i class="fas fa-upload"></i>
                                    <span>Choisir une image</span>
                                </label>
                                <div id="file-name" class="file-name">Aucun fichier sélectionné</div>
                            </div>
                            <small class="form-text text-muted">Formats acceptés: JPG, JPEG, PNG, GIF, WEBP. Taille max: 5MB</small>
                        </div>
                        
                        <div class="form-group">
                            <div id="image-preview" class="image-preview hidden">
                                <img src="/placeholder.svg" alt="Aperçu de l'image" id="preview-img">
                                <button type="button" id="remove-image" class="btn btn-sm btn-admin-danger">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-admin-primary">
                                <i class="fas fa-save"></i>
                                Ajouter la catégorie
                            </button>
                            <a href="list.php" class="btn btn-admin-outline">
                                <i class="fas fa-times"></i>
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../../assets/js/admin.js"></script>
    <script>
        // Prévisualisation de l'image
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('image');
            const fileNameDisplay = document.getElementById('file-name');
            const imagePreview = document.getElementById('image-preview');
            const previewImg = document.getElementById('preview-img');
            const removeButton = document.getElementById('remove-image');
            
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    fileNameDisplay.textContent = file.name;
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                } else {
                    fileNameDisplay.textContent = 'Aucun fichier sélectionné';
                    imagePreview.classList.add('hidden');
                }
            });
            
            removeButton.addEventListener('click', function() {
                fileInput.value = '';
                fileNameDisplay.textContent = 'Aucun fichier sélectionné';
                imagePreview.classList.add('hidden');
            });
        });
    </script>
</body>
</html>
