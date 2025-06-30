<?php
/**
 * Supprimer un produit - Administration
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

// Vérifier si l'ID du produit est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('list.php');
}

$product_id = (int)$_GET['id'];

// Connexion à la base de données
$db = getDBConnection();

// Récupérer le produit
$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('list.php');
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        // Supprimer l'image associée
        if (!empty($product['image'])) {
            $image_path = '../../uploads/products/' . $product['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Supprimer le produit de la base de données
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        
        if ($stmt->execute([$product_id])) {
            // Supprimer aussi des favoris
            $stmt = $db->prepare("DELETE FROM favorites WHERE product_id = ?");
            $stmt->execute([$product_id]);
            
            redirect('list.php?deleted=1');
        } else {
            $error = 'Erreur lors de la suppression du produit.';
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
$page_title = 'Supprimer le Produit';
$page_icon = 'fas fa-trash';
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
    /* delete.php specific styles */
.delete-confirmation {
    max-width: 800px;
    margin: 0 auto;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 2rem;
}

.delete-warning {
    text-align: center;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background-color: #fff8f8;
    border-left: 4px solid #ff5252;
    border-radius: 4px;
}

.delete-warning i {
    font-size: 2.5rem;
    color: #ff5252;
    margin-bottom: 1rem;
}

.delete-warning h2 {
    color: #ff5252;
    margin: 0.5rem 0;
}

.delete-warning p {
    color: #666;
    margin: 0;
}

.product-preview {
    display: flex;
    gap: 2rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background-color: #f9f9f9;
    border-radius: 6px;
}

.product-image {
    flex: 0 0 200px;
}

.product-image img {
    width: 100%;
    height: auto;
    border-radius: 4px;
    object-fit: cover;
}

.product-image .no-image {
    width: 200px;
    height: 200px;
    background: #eee;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #999;
    border-radius: 4px;
}

.product-image .no-image i {
    font-size: 3rem;
    margin-bottom: 0.5rem;
}

.product-details {
    flex: 1;
}

.product-details h3 {
    margin-top: 0;
    color: #333;
    font-size: 1.5rem;
}

.product-description {
    color: #666;
    line-height: 1.6;
    margin: 1rem 0;
}

.product-price {
    font-size: 1.25rem;
    font-weight: bold;
    color: #2e7d32;
    margin: 1rem 0;
}

.product-status {
    margin: 1rem 0;
}

.form-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-admin-danger {
    background-color: #ff5252;
    border-color: #ff5252;
}

.btn-admin-danger:hover {
    background-color: #ff3232;
    border-color: #ff3232;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .product-preview {
        flex-direction: column;
    }
    
    .product-image {
        flex: 1;
        margin-bottom: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>
<body>
    <div class="admin-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include '../includes/header.php'; ?>
            
            <div class="admin-content">
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <span class="alert-message"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="delete-confirmation">
                    <div class="delete-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h2>Confirmer la suppression</h2>
                        <p>Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.</p>
                    </div>
                    
                    <div class="product-preview">
                        <div class="product-image">
                            <?php if (!empty($product['image'])): ?>
                                <img src="../../uploads/products/<?php echo $product['image']; ?>" 
                                     alt="<?php echo htmlspecialchars($product['nom']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                    <span>Aucune image</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-details">
                            <h3><?php echo htmlspecialchars($product['nom']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                            <p class="product-price"><?php echo formatPrice($product['prix']); ?></p>
                            <p class="product-status">
                                Statut : 
                                <?php if ($product['disponible']): ?>
                                    <span class="badge badge-success">Disponible</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Indisponible</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <form method="POST" action="delete.php?id=<?php echo $product_id; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-admin-danger">
                                <i class="fas fa-trash"></i>
                                Confirmer la suppression
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
