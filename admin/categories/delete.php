<?php
require_once '../../config/database.php';
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Vérifier si l'utilisateur est administrateur
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Vérifier l'ID et le token CSRF
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['csrf_token']) || !verifyCSRFToken($_GET['csrf_token'])) {
    redirect('list.php');
}

$category_id = (int)$_GET['id'];

// Connexion à la base de données
$db = getDBConnection();

// Récupérer la catégorie pour avoir le nom de l'image
$stmt = $db->prepare("SELECT image FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if ($category) {
    // Supprimer l'image si elle existe
    if (!empty($category['image'])) {
        $upload_dir = '../../uploads/categories/';
        if (file_exists($upload_dir . $category['image'])) {
            unlink($upload_dir . $category['image']);
        }
    }
    
    // Supprimer la catégorie
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
}

// Rediriger avec un message de succès
$_SESSION['success_message'] = 'La catégorie a été supprimée avec succès.';
redirect('list.php');
?>