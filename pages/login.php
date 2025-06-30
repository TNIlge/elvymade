<?php
/**
 * Page de connexion moderne
 * ElvyMade - Site de prospection de bijoux
 */

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    header('Location: profile.php');
    exit();
}

// Traitement du formulaire de connexion
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($email) || empty($password)) {
            $error = 'Veuillez remplir tous les champs.';
        } elseif (!validateEmail($email)) {
            $error = 'Adresse email invalide.';
        } else {
            $db = getDBConnection();
            $stmt = $db->prepare("SELECT id, nom, email, password, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'active') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nom'];
                    $_SESSION['user_email'] = $user['email'];
                    
                    $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $updateStmt->execute([$user['id']]);
                    
                    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'profile.php';
                    header('Location: ' . $redirect);
                    exit();
                } else {
                    $error = 'Votre compte n\'est pas activé.';
                }
            } else {
                $error = 'Email ou mot de passe incorrect.';
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Elvy.Made</title>
    <meta name="description" content="Connectez-vous à votre compte ElvyMade pour découvrir nos bijoux exclusifs.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern-style.css">
           <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <!-- Contenu principal -->
    <main class="container" style="padding: 4rem 20px;">
        <div class="grid grid-2" style="align-items: center; min-height: 70vh;">
            <!-- Section illustration -->
            <div class="login-illustration fade-in">
                <div style="background: linear-gradient(135deg, var(--primary-light), var(--primary-color)); border-radius: var(--border-radius); padding: 3rem; text-align: center; color: white;">
                    <div style="font-size: 4rem; margin-bottom: 2rem;">
                        <i class="fas fa-gem"></i>
                    </div>
                    <h2 style="font-size: 2rem; margin-bottom: 1rem; font-weight: 600;">Bienvenue chez ElvyMade</h2>
                    <p style="font-size: 1.1rem; opacity: 0.9; line-height: 1.6;">
                        Découvrez notre collection exclusive de bijoux artisanaux du Cameroun. 
                        Connectez-vous pour accéder à vos favoris et profiter d'une expérience personnalisée.
                    </p>
                    <div style="margin-top: 2rem; display: flex; justify-content: center; gap: 1rem;">
                        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-crown" style="font-size: 1.5rem;"></i>
                        </div>
                        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-ring" style="font-size: 1.5rem;"></i>
                        </div>
                        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-heart" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire de connexion -->
            <div class="login-form slide-in">
                <div class="card" style="max-width: 400px; margin: 0 auto;">
                    <div class="card-header text-center">
                        <h1 style="color: var(--primary-color); margin-bottom: 0.5rem;">Connexion</h1>
                        <p style="color: var(--text-light);">Accédez à votre espace personnel</p>
                    </div>
                    
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?php echo $error; ?></span>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="form-group">
                                <label class="form-label" for="email">
                                    <i class="fas fa-envelope"></i>
                                    Adresse email
                                </label>
                                <div class="input-group">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" id="email" name="email" class="form-input" required 
                                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                                           placeholder="votre@email.com">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="password">
                                    <i class="fas fa-lock"></i>
                                    Mot de passe
                                </label>
                                <div class="input-group">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" id="password" name="password" class="form-input" required 
                                           placeholder="Votre mot de passe">
                                    <button type="button" class="btn btn-outline btn-small" onclick="togglePassword('password')" 
                                            style="position: absolute; right: 0.5rem; padding: 0.5rem;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                    <input type="checkbox" name="remember_me" style="accent-color: var(--primary-color);">
                                    <span style="font-size: 0.9rem;">Se souvenir de moi</span>
                                </label>
                                <a href="#" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem;">
                                    Mot de passe oublié ?
                                </a>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-large w-full">
                                <i class="fas fa-sign-in-alt"></i>
                                Se connecter
                            </button>
                        </form>
                    </div>
                    
                    <div class="card-footer text-center">
                        <p style="margin-bottom: 1rem;">Vous n'avez pas de compte ?</p>
                        <a href="register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" 
                           class="btn btn-outline w-full">
                            <i class="fas fa-user-plus"></i>
                            Créer un compte
                        </a>
                        
                        <div style="margin: 1.5rem 0; position: relative;">
                            <div style="height: 1px; background: var(--border-color);"></div>
                            <span style="position: absolute; top: -0.5rem; left: 50%; transform: translateX(-50%); background: white; padding: 0 1rem; color: var(--text-light); font-size: 0.9rem;">ou</span>
                        </div>
                        
                        <a href="../index.php" class="btn btn-outline w-full">
                            <i class="fas fa-home"></i>
                            Continuer sans compte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
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
