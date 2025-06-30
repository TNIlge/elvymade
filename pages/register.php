<?php
/**
 * Page d'inscription moderne
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

// Traitement du formulaire d'inscription
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $nom = sanitizeInput($_POST['nom']);
        $email = sanitizeInput($_POST['email']);
        $telephone = sanitizeInput($_POST['telephone']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($nom) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = 'Tous les champs obligatoires doivent être remplis.';
        } elseif (!validateEmail($email)) {
            $error = 'Adresse email invalide.';
        } elseif (!empty($telephone) && !validateCameroonPhone($telephone)) {
            $error = 'Numéro de téléphone invalide.';
        } elseif (strlen($password) < 6) {
            $error = 'Le mot de passe doit contenir au moins 6 caractères.';
        } elseif ($password !== $confirm_password) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            $db = getDBConnection();
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Cette adresse email est déjà utilisée.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (nom, email, telephone, password, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
                
                if ($stmt->execute([$nom, $email, $telephone, $hashed_password])) {
                    $success = 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.';
                    $nom = $email = $telephone = '';
                } else {
                    $error = 'Une erreur est survenue lors de la création de votre compte.';
                }
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
    <title>Inscription - ElvyMade</title>
    <meta name="description" content="Créez votre compte ElvyMade et découvrez notre collection exclusive de bijoux.">
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
        <div class="grid grid-2" style="align-items: center; min-height: 80vh;">
            <!-- Section illustration -->
            <div class="register-illustration fade-in">
                <div style="background: linear-gradient(135deg, #e3f2fd, var(--primary-light)); border-radius: var(--border-radius); padding: 3rem; text-align: center; position: relative; overflow: hidden;">
                    <!-- Illustration avec panier et téléphone -->
                    <div style="position: relative; z-index: 2;">
                        <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 2rem;">
                            <div style="position: relative;">
                                <!-- Panier -->
                                <div style="width: 120px; height: 80px; background: var(--primary-color); border-radius: 8px; position: relative; margin-right: 2rem;">
                                    <div style="width: 100px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 4px; position: absolute; top: 10px; left: 10px;"></div>
                                    <div style="width: 15px; height: 15px; background: var(--primary-dark); border-radius: 50%; position: absolute; top: -5px; right: 10px;"></div>
                                </div>
                                <!-- Téléphone -->
                                <div style="width: 80px; height: 140px; background: #333; border-radius: 12px; position: relative; box-shadow: var(--shadow-medium);">
                                    <div style="width: 70px; height: 120px; background: #000; border-radius: 8px; position: absolute; top: 10px; left: 5px;"></div>
                                    <div style="width: 30px; height: 4px; background: #666; border-radius: 2px; position: absolute; top: 4px; left: 25px;"></div>
                                </div>
                                <!-- Sacs shopping -->
                                <div style="position: absolute; top: -20px; left: -30px;">
                                    <div style="width: 40px; height: 50px; background: var(--secondary-color); border-radius: 4px; transform: rotate(-15deg);"></div>
                                </div>
                                <div style="position: absolute; bottom: -10px; right: -20px;">
                                    <div style="width: 35px; height: 45px; background: var(--accent-color); border-radius: 4px; transform: rotate(20deg);"></div>
                                </div>
                            </div>
                        </div>
                        
                        <h2 style="font-size: 2rem; margin-bottom: 1rem; font-weight: 600; color: var(--primary-color);">
                            Rejoignez ElvyMade
                        </h2>
                        <p style="font-size: 1.1rem; color: var(--text-light); line-height: 1.6; margin-bottom: 2rem;">
                            Créez votre compte et accédez à notre collection exclusive de bijoux artisanaux. 
                            Sauvegardez vos favoris et recevez nos dernières nouveautés.
                        </p>
                        
                        <!-- Avantages -->
                        <div style="display: grid; gap: 1rem; text-align: left;">
                            <div style="display: flex; align-items: center; gap: 1rem; background: rgba(255,255,255,0.7); padding: 1rem; border-radius: 8px;">
                                <div style="width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div>
                                    <h4 style="margin: 0; color: var(--text-dark);">Favoris personnalisés</h4>
                                    <p style="margin: 0; font-size: 0.9rem; color: var(--text-light);">Sauvegardez vos bijoux préférés</p>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 1rem; background: rgba(255,255,255,0.7); padding: 1rem; border-radius: 8px;">
                                <div style="width: 40px; height: 40px; background: var(--secondary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div>
                                    <h4 style="margin: 0; color: var(--text-dark);">Notifications exclusives</h4>
                                    <p style="margin: 0; font-size: 0.9rem; color: var(--text-light);">Soyez informé des nouveautés</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Éléments décoratifs -->
                    <div style="position: absolute; top: 20px; right: 20px; width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 50%; opacity: 0.5;"></div>
                    <div style="position: absolute; bottom: 30px; left: 30px; width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; opacity: 0.3;"></div>
                </div>
            </div>

            <!-- Formulaire d'inscription -->
            <div class="register-form slide-in">
                <div class="card" style="max-width: 450px; margin: 0 auto;">
                    <div class="card-header text-center">
                        <h1 style="color: var(--primary-color); margin-bottom: 0.5rem;">Créer un compte</h1>
                        <p style="color: var(--text-light);">Rejoignez notre communauté de passionnés</p>
                    </div>
                    
                    <div class="card-body">
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

                        <form method="POST" action="register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="form-group">
                                <label class="form-label" for="nom">
                                    <i class="fas fa-user"></i>
                                    Nom complet <span style="color: var(--primary-color);">*</span>
                                </label>
                                <div class="input-group">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" id="nom" name="nom" class="form-input" required 
                                           value="<?php echo isset($nom) ? htmlspecialchars($nom) : ''; ?>"
                                           placeholder="Votre nom complet">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="email">
                                    <i class="fas fa-envelope"></i>
                                    Adresse email <span style="color: var(--primary-color);">*</span>
                                </label>
                                <div class="input-group">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" id="email" name="email" class="form-input" required 
                                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                                           placeholder="votre@email.com">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="telephone">
                                    <i class="fas fa-phone"></i>
                                    Téléphone
                                </label>
                                <div class="input-group">
                                    <i class="fas fa-phone input-icon"></i>
                                    <input type="tel" id="telephone" name="telephone" class="form-input" 
                                           value="<?php echo isset($telephone) ? htmlspecialchars($telephone) : ''; ?>"
                                           placeholder="+237 6XX XXX XXX">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="password">
                                    <i class="fas fa-lock"></i>
                                    Mot de passe <span style="color: var(--primary-color);">*</span>
                                </label>
                                <div class="input-group">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" id="password" name="password" class="form-input" required 
                                           placeholder="Au moins 6 caractères" minlength="6">
                                    <button type="button" class="btn btn-outline btn-small" onclick="togglePassword('password')" 
                                            style="position: absolute; right: 0.5rem; padding: 0.5rem;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="confirm_password">
                                    <i class="fas fa-lock"></i>
                                    Confirmer le mot de passe <span style="color: var(--primary-color);">*</span>
                                </label>
                                <div class="input-group">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required 
                                           placeholder="Répétez votre mot de passe">
                                    <button type="button" class="btn btn-outline btn-small" onclick="togglePassword('confirm_password')" 
                                            style="position: absolute; right: 0.5rem; padding: 0.5rem;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; font-size: 0.9rem; line-height: 1.5;">
                                    <input type="checkbox" name="accept_terms" required style="accent-color: var(--primary-color); margin-top: 0.2rem;">
                                    <span>
                                        J'accepte les <a href="#" style="color: var(--primary-color); text-decoration: none;">conditions d'utilisation</a> 
                                        et la <a href="#" style="color: var(--primary-color); text-decoration: none;">politique de confidentialité</a>
                                    </span>
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-large w-full">
                                <i class="fas fa-user-plus"></i>
                                Créer mon compte
                            </button>
                        </form>
                    </div>
                    
                    <div class="card-footer text-center">
                        <p style="margin-bottom: 1rem;">Vous avez déjà un compte ?</p>
                        <a href="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" 
                           class="btn btn-outline w-full">
                            <i class="fas fa-sign-in-alt"></i>
                            Se connecter
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

        // Validation en temps réel des mots de passe
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
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
