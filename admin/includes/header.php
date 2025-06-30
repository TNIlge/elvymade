<?php
/**
 * Header de l'administration
 * ElvyMade - Site de prospection d'articles
 */

// Variables par défaut si non définies
if (!isset($page_title)) $page_title = 'Administration';
if (!isset($page_icon)) $page_icon = 'fas fa-cog';
if (!isset($header_actions)) $header_actions = '';
?>

<style>
/* Styles pour le header */
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
    height: 70px;
    background-color: white;
    border-bottom: 1px solid #e0e0e0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.admin-header-left {
    display: flex;
    align-items: center;
}

.sidebar-toggle {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 20px;
    color: #333;
    margin-right: 15px;
    padding: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.sidebar-toggle:hover {
    background-color: #f0f0f0;
}

.admin-title {
    font-size: 20px;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
}

.admin-title i {
    margin-right: 10px;
    color: #3498db;
}

.admin-header-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.admin-search {
    position: relative;
    width: 250px;
}

.admin-search input {
    width: 100%;
    padding: 8px 15px 8px 35px;
    border: 1px solid #e0e0e0;
    border-radius: 20px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.admin-search input:focus {
    outline: none;
    border-color: #3498db;
}

.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #7f8c8d;
    font-size: 14px;
}

.admin-user {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.admin-avatar {
    width: 40px;
    height: 40px;
    background-color: #3498db;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.admin-user-info {
    display: flex;
    flex-direction: column;
}

.admin-user-name {
    font-weight: 600;
    font-size: 14px;
}

.admin-user-role {
    font-size: 12px;
    color: #7f8c8d;
}

/* Header compact pour mobile */
.admin-header-compact {
    display: none;
    justify-content: space-between;
    align-items: center;
    padding: 0 15px;
    height: 60px;
    background-color: white;
    border-bottom: 1px solid #e0e0e0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.menu-toggle-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 20px;
    color: #333;
    padding: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.admin-header-compact-title {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}

/* Styles responsifs */
@media (max-width: 768px) {
    .admin-header {
        display: none;
    }
    
    .admin-header-compact {
        display: flex;
    }
    
    .admin-main {
        margin-left: 0 !important;
    }
}

@media (max-width: 576px) {
    .admin-search {
        display: none;
    }
}
</style>

<!-- Header compact pour mobile -->
<div class="admin-header-compact">
    <button class="menu-toggle-btn" id="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </button>
    <h1 class="admin-header-compact-title"><?php echo htmlspecialchars($page_title); ?></h1>
    <div></div> <!-- Pour l'alignement -->
</div>

<!-- Header principal -->
<header class="admin-header">
    <div class="admin-header-left">
        <button class="sidebar-toggle" id="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="admin-title">
            <i class="<?php echo $page_icon; ?>"></i>
            <?php echo htmlspecialchars($page_title); ?>
        </h1>
    </div>
    
    <div class="admin-header-right">
        
        
        <div class="admin-search">
            <i class="fas fa-search search-icon"></i>
            <input type="text" placeholder="Rechercher...">
        </div>
        
        <div class="admin-user">
            <div class="admin-avatar">
                <?php echo isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'A'; ?>
            </div>
            <div class="admin-user-info">
                <div class="admin-user-name"><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin'; ?></div>
                <div class="admin-user-role">Administrateur</div>
            </div>
        </div>
    </div>
</header>

<script>
// Script pour gérer le menu mobile
document.addEventListener('DOMContentLoaded', function() {
    // Éléments DOM
    const sidebar = document.querySelector('.admin-sidebar');
    const overlay = document.querySelector('.overlay');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    
    // Fonction pour ouvrir/fermer la sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.classList.toggle('sidebar-open');
    }
    
    // Événements pour les boutons de menu
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', toggleSidebar);
    }
    
    // Fermer la sidebar en cliquant sur l'overlay
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        });
    }
    
    // Fermer la sidebar sur les petits écrans quand on clique sur un lien
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('sidebar-open');
            }
        });
    });
    
    // Ajuster la sidebar lors du redimensionnement de la fenêtre
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }
    });
});
</script>
