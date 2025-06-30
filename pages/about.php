<?php
/**
 * Page À Propos moderne
 * ElvyMade - Site de prospection de bijoux
 */

// Inclusion des fichiers de configuration
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À Propos - Elvy.Made</title>
    <meta name="description" content="Découvrez l'histoire d'ElvyMade, votre spécialiste en bijoux de luxe au Cameroun. Notre passion pour l'excellence depuis 2020.">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body<?php echo isLoggedIn() ? ' class="logged-in"' : ''; ?>>
    <?php include '../includes/header.php'; ?>
    
    <!-- Section Hero -->
    <section style="background: linear-gradient(135deg, black 0%, var(--primary-dark) 100%); color: var(--white); padding: var(--spacing-20) 0; position: relative;">
        <div class="container">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-16); align-items: center;">
                <div>
                    <h1 style="font-size: var(--font-size-5xl); font-weight: 700; margin-bottom: var(--spacing-6); line-height: 1.1;">
                        Notre Histoire
                    </h1>
                    <p style="font-size: var(--font-size-xl); margin-bottom: var(--spacing-8); opacity: 0.9; line-height: 1.6;">
                        Lancé en 2020, Elvy.Made est le premier site de prospection de bijoux en perles au Cameroun. 
                        Nous connectons les amateurs de bijoux avec les plus belles créations, 
                        en offrant une expérience unique et personnalisée.
                    </p>
                    <p style="font-size: var(--font-size-lg); opacity: 0.8;">
                        Elvy.Made propose plus d'1 million de bijoux, avec une croissance très rapide. 
                        Nous offrons un assortiment diversifié dans des catégories allant des bagues aux colliers.
                    </p>
                </div>
                <div style="position: relative;">
                    <img src="../assets/images/about.jpg" alt="Équipe ElvyMade" 
                         style="width: 100%; height: auto; border-radius: var(--border-radius-2xl); box-shadow: var(--shadow-xl);">
                </div>
            </div>
        </div>
    </section>

    <!-- Section Statistiques -->
    <section style="padding: var(--spacing-16) 0; background: var(--white);">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--spacing-8); text-align: center;">
                <div style="padding: var(--spacing-6); border-radius: var(--border-radius-xl);">
                    <div style="width: 80px; height: 80px; background: var(--gray-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-4);">
                        <i class="fas fa-users" style="font-size: var(--font-size-2xl); color: var(--gray-700);"></i>
                    </div>
                    <h3 style="font-size: var(--font-size-3xl); font-weight: 700; color: var(--gray-900); margin-bottom: var(--spacing-2);">
                        10.5k
                    </h3>
                    <p style="color: var(--gray-600); font-weight: 500;">Clients actifs sur notre site</p>
                </div>
                
                <div style="padding: var(--spacing-6); border-radius: var(--border-radius-xl); background: var(--primary-color); color: var(--white);">
                    <div style="width: 80px; height: 80px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-4);">
                        <i class="fas fa-dollar-sign" style="font-size: var(--font-size-2xl); color: var(--white);"></i>
                    </div>
                    <h3 style="font-size: var(--font-size-3xl); font-weight: 700; margin-bottom: var(--spacing-2);">
                        300
                    </h3>
                    <p style="opacity: 0.9; font-weight: 500;">Prospections mensuelles</p>
                </div>
                
                <div style="padding: var(--spacing-6); border-radius: var(--border-radius-xl);">
                    <div style="width: 80px; height: 80px; background: var(--gray-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-4);">
                        <i class="fas fa-shopping-bag" style="font-size: var(--font-size-2xl); color: var(--gray-700);"></i>
                    </div>
                    <h3 style="font-size: var(--font-size-3xl); font-weight: 700; color: var(--gray-900); margin-bottom: var(--spacing-2);">
                        5.5k
                    </h3>
                    <p style="color: var(--gray-600); font-weight: 500;">Clients inscrits</p>
                </div>
                
                <div style="padding: var(--spacing-6); border-radius: var(--border-radius-xl);">
                    <div style="width: 80px; height: 80px; background: var(--gray-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-4);">
                        <i class="fas fa-chart-line" style="font-size: var(--font-size-2xl); color: var(--gray-700);"></i>
                    </div>
                    <h3 style="font-size: var(--font-size-3xl); font-weight: 700; color: var(--gray-900); margin-bottom: var(--spacing-2);">
                        25k
                    </h3>
                    <p style="color: var(--gray-600); font-weight: 500;">Ventes annuelles sur notre site</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Équipe -->
    <section style="padding: var(--spacing-20) 0; background: var(--gray-50);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Notre Équipe</h2>
                <p class="section-subtitle">Les passionnés qui font d'ElvyMade une référence</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--spacing-8);">
                <div style="background: var(--white); padding: var(--spacing-6); border-radius: var(--border-radius-xl); text-align: center; box-shadow: var(--shadow);">
                    <img src="/placeholder.svg?height=120&width=120" alt="Elvy Founder" 
                         style="width: 120px; height: 120px; border-radius: 50%; margin: 0 auto var(--spacing-4); object-fit: cover;">
                    <h3 style="font-size: var(--font-size-xl); font-weight: 600; color: var(--gray-900); margin-bottom: var(--spacing-2);">
                        Elvy Made
                    </h3>
                    <p style="color: var(--gray-600); margin-bottom: var(--spacing-4);">Fondatrice & Directrice</p>
                    <div style="display: flex; justify-content: center; gap: var(--spacing-3);">
                        <a href="#" style="color: var(--gray-600); font-size: var(--font-size-lg); transition: var(--transition);">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" style="color: var(--gray-600); font-size: var(--font-size-lg); transition: var(--transition);">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" style="color: var(--gray-600); font-size: var(--font-size-lg); transition: var(--transition);">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                </div>
                
                <div style="background: var(--white); padding: var(--spacing-6); border-radius: var(--border-radius-xl); text-align: center; box-shadow: var(--shadow);">
                    <img src="/placeholder.svg?height=120&width=120" alt="Emma Watson" 
                         style="width: 120px; height: 120px; border-radius: 50%; margin: 0 auto var(--spacing-4); object-fit: cover;">
                    <h3 style="font-size: var(--font-size-xl); font-weight: 600; color: var(--gray-900); margin-bottom: var(--spacing-2);">
                        Emma Watson
                    </h3>
                    <p style="color: var(--gray-600); margin-bottom: var(--spacing-4);">Directrice Marketing</p>
                    <div style="display: flex; justify-content: center; gap: var(--spacing-3);">
                        <a href="#" style="color: var(--gray-600); font-size: var(--font-size-lg); transition: var(--transition);">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" style="color: var(--gray-600); font-size: var(--font-size-lg); transition: var(--transition);">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" style="color: var(--gray-600); font-size: var(--font-size-lg); transition: var(--transition);">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                </div>
                
                <div style="background: var(--white); padding: var(--spacing-6); border-radius: var(--border-radius-xl); text-align: center; box-shadow: var(--shadow);">
                    <img src="/placeholder.svg?height=120&width=120" alt="Will Smith" 
                         style="width: 120px; height: 120px; border-radius: 50%; margin: 0 auto var(--spacing-4); object-fit: cover;">
                    <h3 style="font-size: var(--font-size-xl); font-weight: 600; color: var(--gray-900); margin-bottom: var(--spacing-2);">
                        Will Smith
                    </h3>
                    <p style="color: var(--gray-600); margin-bottom: var(--spacing-4);">Designer Produit</p>
                    <div style="display: flex; justify-content: center; gap: var(--spacing-3);">
                        <a href="#" style="color: var(--gray-600); font-size: var(--font-size-lg); transition: var(--transition);">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" style="color: var(--gray-600); font-size: var(--font-size-lg); transition: var(--transition);">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" style="color: var(--gray-600); font-size: var(--font-size-lg); transition: var(--transition);">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Services -->
    <section style="padding: var(--spacing-20) 0; background: var(--white);">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--spacing-8);">
                <div style="text-align: center; padding: var(--spacing-6);">
                    <div style="width: 80px; height: 80px; background: var(--gray-900); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-4);">
                        <i class="fas fa-shipping-fast" style="color: var(--white); font-size: var(--font-size-xl);"></i>
                    </div>
                    <h3 style="font-size: var(--font-size-xl); font-weight: 600; color: var(--gray-900); margin-bottom: var(--spacing-3);">
                        LIVRAISON GRATUITE ET RAPIDE
                    </h3>
                    <p style="color: var(--gray-600);">
                        Livraison gratuite pour toutes les commandes de plus de 50 000 FCFA
                    </p>
                </div>
                
                <div style="text-align: center; padding: var(--spacing-6);">
                    <div style="width: 80px; height: 80px; background: var(--gray-900); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-4);">
                        <i class="fas fa-headset" style="color: var(--white); font-size: var(--font-size-xl);"></i>
                    </div>
                    <h3 style="font-size: var(--font-size-xl); font-weight: 600; color: var(--gray-900); margin-bottom: var(--spacing-3);">
                        SERVICE CLIENT 24/7
                    </h3>
                    <p style="color: var(--gray-600);">
                        Support client amical 24/7
                    </p>
                </div>
                
                <div style="text-align: center; padding: var(--spacing-6);">
                    <div style="width: 80px; height: 80px; background: var(--gray-900); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-4);">
                        <i class="fas fa-shield-alt" style="color: var(--white); font-size: var(--font-size-xl);"></i>
                    </div>
                    <h3 style="font-size: var(--font-size-xl); font-weight: 600; color: var(--gray-900); margin-bottom: var(--spacing-3);">
                        GARANTIE DE REMBOURSEMENT
                    </h3>
                    <p style="color: var(--gray-600);">
                        Nous remboursons l'argent sous 30 jours
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
    
    <!-- Bouton WhatsApp flottant -->
    <div class="whatsapp-float">
        <a href="#" onclick="openWhatsAppGeneral()" class="whatsapp-btn">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/whatsapp.js"></script>
      <script src="../assets/js/favorites.js"></script>
    <style>
        @media (max-width: 992px) {
            section div[style*="grid-template-columns: repeat(4, 1fr)"] {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: var(--spacing-6) !important;
            }
            
            section div[style*="grid-template-columns: repeat(3, 1fr)"] {
                grid-template-columns: 1fr !important;
                gap: var(--spacing-6) !important;
            }
        }
        
        @media (max-width: 768px) {
            section div[style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
                gap: var(--spacing-8) !important;
                text-align: center;
            }
            
            section div[style*="grid-template-columns: repeat(2, 1fr)"] {
                grid-template-columns: 1fr !important;
            }
        }
        
        /* Hover effects */
        .container a[style*="color: var(--gray-600)"]:hover {
            color: var(--primary-color) !important;
            transform: translateY(-2px);
        }
    </style>
</body>
</html>
