<?php
/**
 * Modifier une catégorie - Administration
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

// Vérifier si l'ID de la catégorie est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('list.php');
}

$category_id = (int)$_GET['id'];

// Connexion à la base de données
$db = getDBConnection();

// Récupérer la catégorie
$stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if (!$category) {
    redirect('list.php');
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
        $nom = sanitizeInput($_POST['nom']);
        $description = sanitizeInput($_POST['description']);
        $current_image = $category['image'];
        $delete_image = isset($_POST['delete_image']) && $_POST['delete_image'] === '1';
        
        // Validation
        if (empty($nom)) {
            $error = 'Le nom de la catégorie est obligatoire.';
        } else {
            // Vérifier si une autre catégorie avec ce nom existe déjà
            $stmt = $db->prepare("SELECT id FROM categories WHERE nom = ? AND id != ?");
            $stmt->execute([$nom, $category_id]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Une autre catégorie avec ce nom existe déjà.';
            } else {
                $image_filename = $current_image;
                
                // Traitement de l'image si elle est fournie
                if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $upload_dir = '../../uploads/categories/';
                    $upload_result = uploadImage($_FILES['image'], $upload_dir);
                    
                    if ($upload_result['success']) {
                        $image_filename = $upload_result['filename'];
                        
                        // Supprimer l'ancienne image si elle existe
                        if (!empty($current_image)) {
                            if (file_exists($upload_dir . $current_image)) {
                                unlink($upload_dir . $current_image);
                            }
                        }
                    } else {
                        $error = $upload_result['message'];
                    }
                } elseif ($delete_image) {
                    // Supprimer l'image si demandé
                    if (!empty($current_image)) {
                        $upload_dir = '../../uploads/categories/';
                        if (file_exists($upload_dir . $current_image)) {
                            unlink($upload_dir . $current_image);
                        }
                        $image_filename = '';
                    }
                }
                
                if (empty($error)) {
                    // Mettre à jour la catégorie
                    $stmt = $db->prepare("UPDATE categories SET nom = ?, description = ?, image = ? WHERE id = ?");
                    
                    if ($stmt->execute([$nom, $description, $image_filename, $category_id])) {
                        $success = 'Catégorie modifiée avec succès.';
                        
                        // Recharger les données de la catégorie
                        $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
                        $stmt->execute([$category_id]);
                        $category = $stmt->fetch();
                    } else {
                        $error = 'Erreur lors de la modification de la catégorie.';
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
$page_title = 'Modifier la Catégorie';
$page_icon = 'fas fa-edit';
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
<style>
    /* Styles spécifiques pour la page d'édition de catégorie */
.admin-form {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.required {
    color: #e74c3c;
}

.form-input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    transition: border-color 0.3s;
}

.form-input:focus {
    border-color: #3498db;
    outline: none;
}

.form-textarea {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    min-height: 120px;
    resize: vertical;
}

.file-input-wrapper {
    position: relative;
    margin-bottom: 10px;
}

.form-input-file {
    width: 0.1px;
    height: 0.1px;
    opacity: 0;
    overflow: hidden;
    position: absolute;
    z-index: -1;
}

.file-input-label {
    display: inline-flex;
    align-items: center;
    padding: 10px 15px;
    background: #f8f9fa;
    border: 1px dashed #ddd;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s;
}

.file-input-label:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

.file-input-label i {
    margin-right: 8px;
    color: #6c757d;
}

.file-name {
    margin-top: 8px;
    font-size: 14px;
    color: #6c757d;
}

.text-muted {
    color: #6c757d;
    font-size: 13px;
}

.image-preview {
    margin-top: 15px;
    position: relative;
    max-width: 300px;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    background: #f8f9fa;
}

.image-preview img {
    max-width: 100%;
    height: auto;
    display: block;
    margin: 0 auto;
}

.image-actions {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #eee;
}

.checkbox-container {
    display: block;
    position: relative;
    padding-left: 30px;
    cursor: pointer;
    user-select: none;
    font-size: 14px;
}

.checkbox-container input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkmark {
    position: absolute;
    top: 0;
    left: 0;
    height: 20px;
    width: 20px;
    background-color: #eee;
    border-radius: 4px;
}

.checkbox-container:hover input ~ .checkmark {
    background-color: #ddd;
}

.checkbox-container input:checked ~ .checkmark {
    background-color: #3498db;
}

.checkmark:after {
    content: "";
    position: absolute;
    display: none;
}

.checkbox-container input:checked ~ .checkmark:after {
    display: block;
}

.checkbox-container .checkmark:after {
    left: 7px;
    top: 3px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.hidden {
    display: none;
}

/* Styles pour les boutons d'action */
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn-delete {
    background-color: #e74c3c;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
    display: inline-flex;
    align-items: center;
    margin-right: 10px;
}

.btn-delete:hover {
    background-color: #c0392b;
}

.btn-delete i {
    margin-right: 8px;
}

/* Confirmation de suppression */
.delete-confirmation {
    display: none;
    margin-top: 20px;
    padding: 15px;
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
}

.delete-confirmation p {
    margin-bottom: 15px;
    color: #721c24;
}

.delete-confirmation-buttons {
    display: flex;
    gap: 10px;
}
</style>
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
                    <form method="POST" action="edit.php?id=<?php echo $category_id; ?>" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="form-group">
                            <label for="nom" class="form-label">Nom de la catégorie <span class="required">*</span></label>
                            <input type="text" id="nom" name="nom" class="form-input" required 
                                   value="<?php echo htmlspecialchars($category['nom']); ?>"
                                   placeholder="Ex: Électronique, Mode, Maison...">
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-textarea" rows="4"
                                      placeholder="Description de la catégorie (optionnel)"><?php echo htmlspecialchars($category['description']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="image" class="form-label">Image de la catégorie</label>
                            <div class="file-input-wrapper">
                                <input type="file" id="image" name="image" class="form-input-file" accept="image/*">
                                <label for="image" class="file-input-label">
                                    <i class="fas fa-upload"></i>
                                    <span>Choisir une image</span>
                                </label>
                                <div id="file-name" class="file-name">
                                    <?php echo $category['image'] ? htmlspecialchars($category['image']) : 'Aucun fichier sélectionné'; ?>
                                </div>
                            </div>
                            <small class="form-text text-muted">Formats acceptés: JPG, JPEG, PNG, GIF, WEBP. Taille max: 5MB</small>
                        </div>
                        
                        <div class="form-group">
                            <?php if (!empty($category['image'])): ?>
                                <div id="current-image" class="image-preview">
                                    <img src="../../uploads/categories/<?php echo htmlspecialchars($category['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($category['nom']); ?>">
                                    <div class="image-actions">
                                        <label class="checkbox-container">
                                            <input type="checkbox" name="delete_image" value="1" id="delete-image">
                                            <span class="checkmark"></span>
                                            Supprimer l'image
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
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
                                Modifier la catégorie
                            </button>

                            <a href="list.php" class="btn btn-admin-outline">
                                <i class="fas fa-times"></i>
                                Annuler
                            </a>
                        </div>
                               <div class="form-group">
    <button type="button" id="btn-delete-category" class="btn btn-delete">
        <i class="fas fa-trash"></i>
        Supprimer la catégorie
    </button>
    
    <div id="delete-confirmation" class="delete-confirmation">
        <p>Êtes-vous sûr de vouloir supprimer définitivement cette catégorie ? Cette action est irréversible.</p>
        <div class="delete-confirmation-buttons">
            <a href="delete.php?id=<?php echo $category_id; ?>&csrf_token=<?php echo $csrf_token; ?>" class="btn btn-admin-danger">
                <i class="fas fa-check"></i>
                Confirmer
            </a>
            <button type="button" id="btn-cancel-delete" class="btn btn-admin-outline">
                <i class="fas fa-times"></i>
                Annuler
            </button>
        </div>
    </div>
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
            const deleteCheckbox = document.getElementById('delete-image');
            const currentImage = document.getElementById('current-image');
            
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    fileNameDisplay.textContent = file.name;
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.classList.remove('hidden');
                        
                        // Cacher l'image actuelle si elle existe
                        if (currentImage) {
                            currentImage.classList.add('hidden');
                        }
                        
                        // Décocher la case "Supprimer l'image"
                        if (deleteCheckbox) {
                            deleteCheckbox.checked = false;
                        }
                    };
                    reader.readAsDataURL(file);
                } else {
                    fileNameDisplay.textContent = 'Aucun fichier sélectionné';
                    imagePreview.classList.add('hidden');
                    
                    // Réafficher l'image actuelle si elle existe
                    if (currentImage) {
                        currentImage.classList.remove('hidden');
                    }
                }
            });
            
            removeButton.addEventListener('click', function() {
                fileInput.value = '';
                fileNameDisplay.textContent = 'Aucun fichier sélectionné';
                imagePreview.classList.add('hidden');
                
                // Réafficher l'image actuelle si elle existe
                if (currentImage) {
                    currentImage.classList.remove('hidden');
                }
            });
            
            // Gérer la case à cocher "Supprimer l'image"
            if (deleteCheckbox) {
                deleteCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        // Désactiver l'input file
                        fileInput.disabled = true;
                        fileInput.parentElement.classList.add('disabled');
                    } else {
                        // Réactiver l'input file
                        fileInput.disabled = false;
                        fileInput.parentElement.classList.remove('disabled');
                    }
                });
            }
        });        document.getElementById('btn-delete-category').addEventListener('click', function() {
    document.getElementById('delete-confirmation').style.display = 'block';
});

document.getElementById('btn-cancel-delete').addEventListener('click', function() {
    document.getElementById('delete-confirmation').style.display = 'none';
});
        
    </script>
</body>
</html>
