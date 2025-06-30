<?php
/**
 * Sidebar de l'administration
 * ElvyMade - Site de prospection d'articles
 */

// Déterminer la page active pour la navigation
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Fonction pour déterminer si un lien est actif
function isActiveLink($page, $dir = '') {
    global $current_page, $current_dir;
    
    if ($dir && $current_dir === $dir) {
        return true;
    }
    
    if ($page === $current_page) {
        return true;
    }
    
    return false;
}

// Déterminer le chemin relatif vers la racine admin
$admin_root = '';
if ($current_dir === 'admin') {
    $admin_root = '';
} else {
    $admin_root = '../';
}
?>



<aside class="admin-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-store"></i>
        </div>
        <div class="sidebar-title">
            <h3>Elvy.Made</h3>
            <p class="sidebar-subtitle">Administration</p>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Menu Principal</div>
            <div class="nav-item">
                <a href="<?php echo $admin_root; ?>index.php" class="nav-link <?php echo isActiveLink('index.php') ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-chart-bar"></i>
                    <span>Tableau de bord</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="<?php echo $admin_root; ?>products/list.php" class="nav-link <?php echo isActiveLink('', 'products') ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-box"></i>
                    <span>Gestion des produits</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="<?php echo $admin_root; ?>categories/list.php" class="nav-link <?php echo isActiveLink('', 'categories') ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-tags"></i>
                    <span>Catégories</span>
                </a>
            </div>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Utilisateurs</div>
            <div class="nav-item">
                <a href="<?php echo $admin_root; ?>users/list.php" class="nav-link <?php echo isActiveLink('', 'users') ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-users"></i>
                    <span>Gestion des utilisateurs</span>
                </a>
            </div>
              <div class="nav-item">
                <a href="<?php echo $admin_root; ?>messages/list.php" class="nav-link <?php echo isActiveLink('', 'users') ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-users"></i>
                    <span>messages</span>
                </a>
            </div>
        </div>
        
        <!-- <div class="nav-section">
            <div class="nav-section-title">Communication</div>
            <div class="nav-item">
                <a href="<?php echo $admin_root; ?>messages/list.php" class="nav-link <?php echo isActiveLink('', 'messages') ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-envelope"></i>
                    <span>Messages de contact</span>
                    <?php if (isset($unread_messages) && $unread_messages > 0): ?>
                        <span class="badge badge-danger"><?php echo $unread_messages; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div> -->
        
        <div class="nav-section">
            <div class="nav-section-title">Configuration</div>
            <div class="nav-item">
                <a href="<?php echo $admin_root; ?>settings/general.php" class="nav-link <?php echo isActiveLink('general.php') ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-cog"></i>
                    <span>Paramètres généraux</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?php echo $admin_root; ?>settings/whatsapp.php" class="nav-link <?php echo isActiveLink('whatsapp.php') ? 'active' : ''; ?>">
                    <i class="nav-icon fab fa-whatsapp"></i>
                    <span>Configuration WhatsApp</span>
                </a>
            </div>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Système</div>
            <div class="nav-item">
                <a href="<?php echo $admin_root; ?>logout.php" class="nav-link">
                    <i class="nav-icon fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </div>
    </nav>
</aside>

<!-- Overlay pour mobile -->
<div class="overlay"></div>
