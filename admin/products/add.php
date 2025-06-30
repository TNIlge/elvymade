<?php
/**
 * Ajouter un produit - Administration
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
        $prix = (float)$_POST['prix'];
        $category_id = (int)$_POST['category_id'];
        $ville = sanitizeInput($_POST['ville']);
        $disponible = isset($_POST['disponible']) ? 1 : 0;
        
        // Validation
        if (empty($nom) || empty($description) || $prix <= 0 || $category_id <= 0) {
            $error = 'Tous les champs obligatoires doivent être remplis correctement.';
        } else {
            // Gestion de l'upload d'image
            $image_name = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../../uploads/products/';
                
                // Créer le dossier s'il n'existe pas
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_info = pathinfo($_FILES['image']['name']);
                $extension = strtolower($file_info['extension']);
                
                // Vérifier l'extension
                if (in_array($extension, ALLOWED_IMAGE_TYPES)) {
                    // Générer un nom unique
                    $image_name = uniqid() . '.' . $extension;
                    $upload_path = $upload_dir . $image_name;
                    
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $error = 'Erreur lors de l\'upload de l\'image.';
                    }
                } else {
                    $error = 'Format d\'image non autorisé. Formats acceptés : ' . implode(', ', ALLOWED_IMAGE_TYPES);
                }
            }
            
            if (empty($error)) {
                // Insérer le produit
                $stmt = $db->prepare("
                    INSERT INTO products (nom, description, prix, category_id, ville, image, disponible, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                if ($stmt->execute([$nom, $description, $prix, $category_id, $ville, $image_name, $disponible])) {
                    $success = 'Produit ajouté avec succès.';
                    // Réinitialiser les variables
                    $nom = $description = $ville = '';
                    $prix = 0;
                    $category_id = 0;
                    $disponible = 1;
                } else {
                    $error = 'Erreur lors de l\'ajout du produit.';
                    // Supprimer l'image uploadée en cas d'erreur
                    if (!empty($image_name) && file_exists($upload_dir . $image_name)) {
                        unlink($upload_dir . $image_name);
                    }
                }
            }
        }
    }
}

// Récupérer les catégories
$categories_stmt = $db->query("SELECT * FROM categories ORDER BY nom ASC");
$categories = $categories_stmt->fetchAll();

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
$page_title = 'Ajouter un Produit';
$page_icon = 'fas fa-plus';
$header_actions = '<a href="list.php" class="btn btn-admin-outline"><i class="fas fa-arrow-left"></i> Retour à la liste</a>';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Administration Elvy.Made</title>
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
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nom" class="form-label">Nom du produit <span class="required">*</span></label>
                                <input type="text" id="nom" name="nom" class="form-input" required 
                                       value="<?php echo isset($nom) ? htmlspecialchars($nom) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="category_id" class="form-label">Catégorie <span class="required">*</span></label>
                                <select id="category_id" name="category_id" class="form-select" required>
                                    <option value="">Sélectionnez une catégorie</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo (isset($category_id) && $category_id == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Description <span class="required">*</span></label>
                            <textarea id="description" name="description" class="form-textarea" rows="5" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="prix" class="form-label">Prix (FCFA) <span class="required">*</span></label>
                                <input type="number" id="prix" name="prix" class="form-input price-input" 
                                       min="0" step="0.01" required 
                                       value="<?php echo isset($prix) ? $prix : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="ville" class="form-label">Ville</label>
                                <select id="ville" name="ville" class="form-select">
                                    <option value="">Sélectionnez une ville</option>
                                    <?php foreach (CAMEROON_CITIES as $city): ?>
                                        <option value="<?php echo $city; ?>" 
                                                <?php echo (isset($ville) && $ville === $city) ? 'selected' : ''; ?>>
                                            <?php echo $city; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="image" class="form-label">Image du produit</label>
                            <input type="file" id="image" name="image" class="form-input image-upload" 
                                   accept="image/*" data-preview="#image-preview">
                            <small>Formats acceptés : <?php echo implode(', ', ALLOWED_IMAGE_TYPES); ?>. Taille max : <?php echo (MAX_UPLOAD_SIZE / 1024 / 1024); ?>MB</small>
                            <div id="image-preview" class="image-preview"></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="disponible" <?php echo (!isset($disponible) || $disponible) ? 'checked' : ''; ?>>
                                <span class="checkmark"></span>
                                Produit disponible
                            </label>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-admin-primary">
                                <i class="fas fa-save"></i>
                                Ajouter le produit
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
</body>
</html>
