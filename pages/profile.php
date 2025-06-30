<?php
/**
 * Page de profil utilisateur moderne
 * ElvyMade - Site de prospection de bijoux
 */

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

// Récupérer les informations de l'utilisateur
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Traitement des formulaires
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            $error = 'Erreur de sécurité. Veuillez réessayer.';
        } else {
            $nom = sanitizeInput($_POST['nom']);
            $telephone = sanitizeInput($_POST['telephone']);
            $avatar = $user['avatar']; // Valeur par défaut (avatar existant)

            // Gestion de l'upload de l'avatar
            if (!empty($_FILES['avatar']['name'])) {
                $uploadDir = '../uploads/avatars/';
                
                // Créer le répertoire s'il n'existe pas
                if (!file_exists($uploadDir)) {
                    if (!mkdir($uploadDir, 0755, true)) {
                        $error = 'Impossible de créer le répertoire de stockage.';
                    }
                }
                
                // Vérifications de sécurité
                if (empty($error)) {
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                    $maxSize = 5 * 1024 * 1024; // 5MB
                    
                    if (!in_array($extension, $allowedExtensions)) {
                        $error = 'Format de fichier non supporté. Utilisez JPG, PNG, GIF ou WEBP.';
                    } elseif ($_FILES['avatar']['size'] > $maxSize) {
                        $error = 'Le fichier est trop volumineux (max 5MB).';
                    } elseif ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                        $error = 'Erreur lors du téléchargement du fichier.';
                    }
                    
                    if (empty($error)) {
                        $filename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
                        $targetPath = $uploadDir . $filename;
                        
                        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
                            // Supprimer l'ancien avatar s'il existe
                            if (!empty($user['avatar']) && file_exists($uploadDir . $user['avatar'])) {
                                unlink($uploadDir . $user['avatar']);
                            }
                            $avatar = $filename;
                        } else {
                            $error = 'Erreur lors du déplacement du fichier uploadé.';
                        }
                    }
                }
            }

            if (empty($error)) {
                $stmt = $db->prepare("UPDATE users SET nom = ?, telephone = ?, avatar = ?, updated_at = NOW() WHERE id = ?");
                
                if ($stmt->execute([$nom, $telephone, $avatar, $_SESSION['user_id']])) {
                    $_SESSION['user_name'] = $nom;
                    $_SESSION['user_avatar'] = $avatar;
                    $success = 'Votre profil a été mis à jour avec succès.';
                    // Rafraîchir les données utilisateur
                    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $error = 'Une erreur est survenue lors de la mise à jour.';
                }
            }
        }
    }
}

// Récupérer les statistiques de l'utilisateur
$stmt = $db->prepare("SELECT COUNT(*) as total_favorites FROM favorites WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Elvy.Made</title>
    <meta name="description" content="Gérez votre profil ElvyMade et vos préférences.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>../assets/css/modern-style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>../assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>../assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .avatar-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-color);
        }

        .avatar-initials {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            border: 3px solid var(--primary-color);
        }

        .profile-tab {
            display: none;
        }

        .profile-tab.active {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sidebar-menu a.active {
            background-color: rgba(var(--primary-color-rgb), 0.1);
            color: var(--primary-color);
            border-left: 3px solid var(--primary-color);
        }
    </style>
</head>

<body>

    <?php include '../includes/header.php'; ?>
    <!-- Fil d'Ariane -->
    <div class="container" style="padding-top: 2rem;">
        <nav class="breadcrumb">
            <a href="../index.php">Accueil</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Mon Profil</span>
        </nav>
    </div>

    <!-- Contenu principal -->
    <main class="container" style="padding: 2rem 20px 4rem;">
        <div class="grid grid-2" style="gap: 3rem; align-items: start;">
            <!-- Sidebar de navigation -->
            <div class="profile-sidebar fade-in">
                <!-- Carte profil utilisateur -->
                <div class="card mb-3">
                    <div class="card-body text-center">
                        <div class="avatar-container" style="margin: 0 auto 1rem;">
                           <?php if (!empty($user['avatar'])): ?>
                                <img src="../uploads/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" 
                                     alt="Avatar de <?php echo htmlspecialchars($user['nom']); ?>" 
                                     class="avatar-image">
                            <?php else: ?>
                                <div class="avatar-initials">
                                    <?php echo strtoupper(substr($user['nom'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <button type="button" class="btn btn-small btn-outline" style="margin-top: 0.5rem;" onclick="document.getElementById('avatar-upload').click()">
                                <i class="fas fa-camera"></i> Changer
                            </button>
                        </div>
                        <h3 style="margin-bottom: 0.5rem; color: var(--text-dark);"><?php echo htmlspecialchars($user['nom']); ?></h3>
                        <p style="color: var(--text-light); margin-bottom: 1rem;"><?php echo htmlspecialchars($user['email']); ?></p>
                        <div style="display: flex; justify-content: center; gap: 2rem; margin-bottom: 1rem;">
                            <div class="text-center">
                                <div style="font-size: 1.5rem; font-weight: 600; color: var(--primary-color);"><?php echo $stats['total_favorites']; ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-light);">Favoris</div>
                            </div>
                            <div class="text-center">
                                <div style="font-size: 1.5rem; font-weight: 600; color: var(--primary-color);">
                                    <?php echo date('Y') - date('Y', strtotime($user['created_at'])) + 1; ?>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-light);">Années</div>
                            </div>
                        </div>
                        <p style="font-size: 0.9rem; color: var(--text-light);">
                            Membre depuis le <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                        </p>
                    </div>
                </div>

                <!-- Menu de navigation -->
                <div class="sidebar">
                    <ul class="sidebar-menu">
                        <li>
                            <a href="#" onclick="showTab('profile-info')" class="active" id="profile-info-tab">
                                <i class="fas fa-user"></i>
                                Informations personnelles
                            </a>
                        </li>
                        <li>
                            <a href="#" onclick="showTab('change-password')" id="change-password-tab">
                                <i class="fas fa-lock"></i>
                                Changer le mot de passe
                            </a>
                        </li>
                        <li>
                            <a href="favorites.php">
                                <i class="fas fa-heart"></i>
                                Mes favoris (<?php echo $stats['total_favorites']; ?>)
                            </a>
                        </li>
                        <li>
                            <a href="../includes/logout.php" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')" style="color: var(--primary-color);">
                                <i class="fas fa-sign-out-alt"></i>
                                Se déconnecter
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
<style>
    .avatar-container {
    position: relative;
    width: fit-content;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.avatar-image {
    width: 150px; /* Taille agrandie de l'image */
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--primary-color);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.avatar-initials {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    font-weight: bold;
    border: 3px solid var(--primary-color);
}

.btn-outline {
   
    color: var(--primary-color);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    transition: all 0.3s ease;
}

.btn-outline:hover {
    background-color: var(--primary-color);
    color: white;
}
</style>
            <!-- Contenu principal -->
            <div class="profile-content slide-in">
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

                <!-- Onglet Informations personnelles -->
                <div id="profile-info" class="profile-tab active">
                    <div class="card">
                        <div class="card-header">
                            <h2 style="margin: 0; color: var(--primary-color);">
                                <i class="fas fa-user"></i>
                                Informations personnelles
                            </h2>
                            <p style="margin: 0.5rem 0 0; color: var(--text-light);">Modifiez vos informations de profil</p>
                        </div>

                        <div class="card-body">
                            <form method="POST" action="profile.php" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="update_profile" value="1">
                                <input type="file" id="avatar-upload" name="avatar" accept="image/*" style="display: none;" onchange="previewAvatar(this)">

                                <div class="form-group">
                                    <label class="form-label" for="nom">
                                        <i class="fas fa-user"></i>
                                        Nom complet
                                    </label>
                                    <div class="input-group">
                                        <i class="fas fa-user input-icon"></i>
                                        <input type="text" id="nom" name="nom" class="form-input" required
                                            value="<?php echo htmlspecialchars($user['nom']); ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="email">
                                        <i class="fas fa-envelope"></i>
                                        Adresse email
                                    </label>
                                    <div class="input-group">
                                        <i class="fas fa-envelope input-icon"></i>
                                        <input type="email" id="email" class="form-input"
                                            value="<?php echo htmlspecialchars($user['email']); ?>"
                                            disabled style="background: var(--background-light); color: var(--text-light);">
                                    </div>
                                    <small style="color: var(--text-light); font-size: 0.8rem;">
                                        <i class="fas fa-info-circle"></i>
                                        L'adresse email ne peut pas être modifiée
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="telephone">
                                        <i class="fas fa-phone"></i>
                                        Téléphone
                                    </label>
                                    <div class="input-group">
                                        <i class="fas fa-phone input-icon"></i>
                                        <input type="tel" id="telephone" name="telephone" class="form-input"
                                            value="<?php echo htmlspecialchars($user['telephone']); ?>"
                                            placeholder="+237 6XX XXX XXX">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-large">
                                    <i class="fas fa-save"></i>
                                    Sauvegarder les modifications
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Onglet Changer le mot de passe -->
                <div id="change-password" class="profile-tab">
                    <div class="card">
                        <div class="card-header">
                            <h2 style="margin: 0; color: var(--primary-color);">
                                <i class="fas fa-lock"></i>
                                Changer le mot de passe
                            </h2>
                            <p style="margin: 0.5rem 0 0; color: var(--text-light);">Modifiez votre mot de passe pour sécuriser votre compte</p>
                        </div>

                        <div class="card-body">
                            <form method="POST" action="profile.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="change_password" value="1">

                                <div class="form-group">
                                    <label class="form-label" for="current_password">
                                        <i class="fas fa-lock"></i>
                                        Mot de passe actuel
                                    </label>
                                    <div class="input-group">
                                        <i class="fas fa-lock input-icon"></i>
                                        <input type="password" id="current_password" name="current_password" class="form-input" required>
                                        <button type="button" class="btn btn-outline btn-small" onclick="togglePassword('current_password')"
                                            style="position: absolute; right: 0.5rem; padding: 0.5rem;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="new_password">
                                        <i class="fas fa-lock"></i>
                                        Nouveau mot de passe
                                    </label>
                                    <div class="input-group">
                                        <i class="fas fa-lock input-icon"></i>
                                        <input type="password" id="new_password" name="new_password" class="form-input" required
                                            minlength="6" placeholder="Au moins 6 caractères">
                                        <button type="button" class="btn btn-outline btn-small" onclick="togglePassword('new_password')"
                                            style="position: absolute; right: 0.5rem; padding: 0.5rem;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="confirm_password">
                                        <i class="fas fa-lock"></i>
                                        Confirmer le nouveau mot de passe
                                    </label>
                                    <div class="input-group">
                                        <i class="fas fa-lock input-icon"></i>
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                                        <button type="button" class="btn btn-outline btn-small" onclick="togglePassword('confirm_password')"
                                            style="position: absolute; right: 0.5rem; padding: 0.5rem;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-large">
                                    <i class="fas fa-key"></i>
                                    Changer le mot de passe
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 style="margin: 0; color: var(--text-dark);">
                            <i class="fas fa-bolt"></i>
                            Actions rapides
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-2" style="gap: 1rem;">
                            <a href="favorites.php" class="btn btn-outline">
                                <i class="fas fa-heart"></i>
                                Voir mes favoris (<?php echo $stats['total_favorites']; ?>)
                            </a>
                            <a href="search.php" class="btn btn-outline">
                                <i class="fas fa-search"></i>
                                Découvrir des bijoux
                            </a>
                            <a href="../index.php" class="btn btn-outline">
                                <i class="fas fa-home"></i>
                                Retour à l'accueil
                            </a>
                            <a href="contact.php" class="btn btn-outline">
                                <i class="fas fa-envelope"></i>
                                Nous contacter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
          function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const avatarContainer = document.querySelector('.avatar-container');
                    avatarContainer.innerHTML = `
                        <img src="${e.target.result}" class="avatar-image">
                        <button type="button" class="btn btn-outline btn-avatar-change" onclick="document.getElementById('avatar-upload').click()">
                            <i class="fas fa-camera"></i> Changer
                        </button>
                    `;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        function showTab(tabId) {
            // Masquer tous les onglets
            document.querySelectorAll('.profile-tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Désactiver tous les liens de navigation
            document.querySelectorAll('.sidebar-menu a').forEach(link => {
                link.classList.remove('active');
            });

            // Afficher l'onglet sélectionné
            document.getElementById(tabId).classList.add('active');

            // Activer le lien correspondant
            document.getElementById(tabId + '-tab').classList.add('active');
        }

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentElement.querySelector('.btn i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const avatarContainer = document.querySelector('.avatar-container');
                    avatarContainer.innerHTML = `
                        <img src="${e.target.result}" class="avatar-image">
                        <button type="button" class="btn btn-small btn-outline" style="margin-top: 0.5rem;" onclick="document.getElementById('avatar-upload').click()">
                            <i class="fas fa-camera"></i> Changer
                        </button>
                    `;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Validation en temps réel des mots de passe
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;

            if (confirmPassword && newPassword !== confirmPassword) {
                this.setCustomValidity('Les mots de passe ne correspondent pas');
                this.style.borderColor = 'var(--primary-color)';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = 'var(--border-color)';
            }
        });

        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in, .slide-in');
            elements.forEach((el, index) => {
                el.style.animationDelay = `${index * 0.2}s`;
            });
        });
    </script>
</body>

</html>