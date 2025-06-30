<?php
/**
 * Page de connexion administrateur
 * ElvyMade - Site de prospection d'articles
 */

// Inclusion des fichiers de configuration
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';



// Rediriger si déjà connecté en tant qu'admin
if (isLoggedIn() && isAdmin()) {
    redirect('index.php');
}

// Traitement du formulaire de connexion
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        // Récupérer et nettoyer les données
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;
        
        // Valider l'email
        if (!validateEmail($email)) {
            $error = 'Adresse email invalide.';
        } else {
            // Connexion à la base de données
            $db = getDBConnection();
            
            // Rechercher l'administrateur
            $stmt = $db->prepare("SELECT id, nom, email, password, role FROM users WHERE email = ? AND role = 'admin'");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Connexion réussie
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['user_name'] = $admin['nom'];
                $_SESSION['user_email'] = $admin['email'];
                $_SESSION['user_role'] = $admin['role'];
                
                // Si "Se souvenir de moi" est coché
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60); // 30 jours
                    
                    // Enregistrer le token en base de données
                    $stmt = $db->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                    $stmt->execute([$admin['id'], $token, date('Y-m-d H:i:s', $expires)]);
                    
                    // Définir le cookie
                    setcookie('remember_token', $token, $expires, '/', '', false, true);
                }
                
                // Redirection vers le tableau de bord
                redirect('index.php');
            } else {
                $error = 'Email ou mot de passe incorrect, ou vous n\'êtes pas administrateur.';
            }
        }
    }
}

// Générer un token CSRF
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administrateur - ElvyMade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>
    /* admin.css - Styles spécifiques à l'interface d'administration */

/* Base */
.admin-login-page {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f5f7fa;
    margin: 0;
    padding: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #333;
}

.admin-login-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

.admin-login-wrapper {
    display: flex;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    height: 600px;
}

/* Côté gauche - Branding */
.admin-login-brand {
    width: 45%;
    background: linear-gradient(135deg, #6e48aa 0%, #9d50bb 100%);
    color: white;
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.brand-content {
    text-align: center;
}

.brand-logo {
    font-size: 50px;
    margin-bottom: 20px;
}

.brand-content h1 {
    font-size: 28px;
    margin-bottom: 10px;
    font-weight: 700;
}

.brand-content p {
    font-size: 16px;
    opacity: 0.9;
}

.brand-features {
    margin-top: 40px;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding: 10px;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.feature-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.feature-item i {
    font-size: 20px;
    margin-right: 15px;
    width: 30px;
    text-align: center;
}

.feature-item span {
    font-size: 15px;
}

/* Côté droit - Formulaire */
.admin-login-form-container {
    width: 55%;
    padding: 50px;
    display: flex;
    align-items: center;
}

.admin-login-form {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
}

.form-header {
    text-align: center;
    margin-bottom: 30px;
}

.form-header h2 {
    font-size: 24px;
    color: #444;
    margin-bottom: 10px;
}

.form-header p {
    color: #777;
    font-size: 14px;
}

/* Form elements */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #555;
    font-size: 14px;
}

.input-icon {
    position: relative;
}

.input-icon i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
}

.input-icon input {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    transition: all 0.3s;
}

.input-icon input:focus {
    border-color: #9d50bb;
    outline: none;
    box-shadow: 0 0 0 3px rgba(157, 80, 187, 0.1);
}

.password-toggle {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    padding: 0;
}

/* Form options */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    font-size: 13px;
}

.remember-me {
    display: flex;
    align-items: center;
}

.remember-me input {
    margin-right: 8px;
}

.forgot-password {
    color: #9d50bb;
    text-decoration: none;
}

.forgot-password:hover {
    text-decoration: underline;
}

/* Button */
.btn {
    display: inline-block;
    padding: 12px 20px;
    border-radius: 5px;
    font-size: 15px;
    font-weight: 500;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
}

.btn-block {
    display: block;
    width: 100%;
}

.btn-admin-primary {
    background-color: #6e48aa;
    color: white;
}

.btn-admin-primary:hover {
    background-color: #5d3a99;
}

/* Form footer */
.form-footer {
    margin-top: 25px;
    text-align: center;
}

.back-to-site {
    color: #777;
    text-decoration: none;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
}

.back-to-site i {
    margin-right: 8px;
}

.back-to-site:hover {
    color: #6e48aa;
}

/* Alert */
.alert {
    padding: 12px 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-error {
    background-color: #fdecea;
    color: #d32f2f;
}

.alert-message {
    display: flex;
    align-items: center;
}

/* Responsive */
@media (max-width: 992px) {
    .admin-login-wrapper {
        flex-direction: column;
        height: auto;
    }
    
    .admin-login-brand,
    .admin-login-form-container {
        width: 100%;
    }
    
    .admin-login-brand {
        padding: 30px;
    }
    
    .admin-login-form-container {
        padding: 30px;
    }
}

@media (max-width: 576px) {
    .admin-login-brand,
    .admin-login-form-container {
        padding: 20px;
    }
    
    .form-options {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .forgot-password {
        margin-top: 10px;
    }
}
</style>
<body class="admin-login-page">
    <div class="admin-login-container">
        <div class="admin-login-wrapper">
            <!-- Côté gauche avec branding -->
            <div class="admin-login-brand">
                <div class="brand-content">
                    <div class="brand-logo">
                        <i class="fas fa-store"></i>
                    </div>
                    <h1>ElvyMade</h1>
                    <p>Panneau d'administration</p>
                </div>
                <div class="brand-features">
                    <div class="feature-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>Tableau de bord complet</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-box"></i>
                        <span>Gestion des produits</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-users"></i>
                        <span>Gestion des utilisateurs</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-cog"></i>
                        <span>Gestion des paramètres</span>
                    </div>
                </div>
            </div>
            
            <!-- Côté droit avec formulaire -->
            <div class="admin-login-form-container">
                <div class="admin-login-form">
                    <div class="form-header">
                        <h2>Connexion Administrateur</h2>
                        <p>Entrez vos identifiants pour accéder au panneau d'administration</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <span class="alert-message"><?php echo $error; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="login.php">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="form-group">
                            <label for="email">Nom d'utilisateur</label>
                            <div class="input-icon">
                                <i class="fas fa-user"></i>
                                <input type="email" id="email" name="email" placeholder="admin@elvymade.cm" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Mot de passe</label>
                            <div class="input-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="password-icon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-options">
                            <div class="remember-me">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">Se souvenir de moi</label>
                            </div>
                            <a href="forgot-password.php" class="forgot-password">Mot de passe oublié ?</a>
                        </div>
                        
                        <button type="submit" class="btn btn-admin-primary btn-block">Se connecter</button>
                    </form>
                    
                    <div class="form-footer">
                        <a href="../index.php" class="back-to-site">
                            <i class="fas fa-arrow-left"></i>
                            Retour au site
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle du mot de passe
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }
    </script>
</body>
</html>
