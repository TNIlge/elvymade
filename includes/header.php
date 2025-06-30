<?php
/**
 * En-tête moderne pour ElvyMade
 * Design inspiré des maquettes avec navigation améliorée
 */
?>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>../assets/css/modern-style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>../assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>../assets/css/responsive.css">


    <!-- Menu mobile -->
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-content">
        <div class="mobile-menu-header">
            <a href="<?php echo SITE_URL; ?>">
                <img src="<?php echo SITE_URL; ?>../assets/images/logo-elvymade.png" alt="ElvyMade Logo" style="height: 40px;">
            </a>
            <button class="mobile-menu-close" onclick="toggleMobileMenu()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <ul class="mobile-nav">
            <li class="nav-item">
                <a href="<?php echo SITE_URL; ?>" class="nav-link">Accueil</a>
            </li>
            <li class="nav-item">
                <a href="<?php echo SITE_URL; ?>../pages/product.php" class="nav-link">Bijoux</a>
            </li>
            <li class="nav-item">
                <a href="<?php echo SITE_URL; ?>../pages/category.php" class="nav-link">Catégories</a>
            </li>
            <li class="nav-item">
                <a href="<?php echo SITE_URL; ?>../pages/about.php" class="nav-link">À Propos</a>
            </li>
            <li class="nav-item">
                <a href="<?php echo SITE_URL; ?>../pages/contact.php" class="nav-link">Contact</a>
            </li>
        </ul>
        
       
        
        <div class="mobile-actions">
            <a href="<?php echo SITE_URL; ?>../pages/favorites.php" class="action-btn" style="display: block; margin-bottom: 1rem;">
                <i class="fas fa-heart"></i> Mes Favoris 
                <span class="mobile-favorite-count"><?php echo isLoggedIn() ? getFavoritesCount($_SESSION['user_id']) : 0; ?></span>
            </a>
            
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo SITE_URL; ?>../pages/profile.php" class="action-btn" style="display: block; margin-bottom: 1rem;">
                    <i class="fas fa-user"></i> Mon Profil
                </a>
                <a href="<?php echo SITE_URL; ?>../includes/logout.php" class="action-btn" style="display: block; color: var(--error-color);">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>../pages/login.php" class="action-btn" style="display: block;">
                    <i class="fas fa-user"></i> Connexion
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>


<header class="main-header">
    <nav class="navbar">
        <div class="container">
            <!-- Logo -->
            <div class="navbar-brand">
                <a href="<?php echo SITE_URL; ?>">
                    <img src="<?php echo SITE_URL; ?>../assets/images/logo-elvymade.png" alt="ElvyMade Logo" class="site-logo">
                   
                </a>
            </div>

            <!-- Navigation principale -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>" class="nav-link">Accueil</a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>../pages/product.php" class="nav-link">Bijoux</a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>../pages/category.php" class="nav-link">Catégories</a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>../pages/about.php" class="nav-link">À Propos</a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>../pages/contact.php" class="nav-link">Contact</a>
                </li>
            </ul>

            <!-- Barre de recherche -->
            <div class="search-bar">
                <form action="<?php echo SITE_URL; ?>../pages/search.php" method="GET" class="search-form">
                    <input type="text" name="q" placeholder="Rechercher des bijoux..." class="search-input">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <!-- Actions utilisateur -->
            <div class="navbar-actions">
                <!-- Favoris -->
                <a href="<?php echo SITE_URL; ?>../pages/favorites.php" class="action-btn" title="Mes favoris">
                    <i class="fas fa-heart"></i>
                    <span class="favorite-count"><?php echo isLoggedIn() ? getFavoritesCount($_SESSION['user_id']) : 0; ?></span>
                </a>

                <!-- Compte utilisateur -->
                <?php if (isLoggedIn()): ?>
                    <div class="user-menu" style="position: relative;">
                        <button class="action-btn user-toggle" onclick="toggleUserMenu()">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </button>
                        <div class="user-dropdown" id="userDropdown" style="
                            position: absolute;
                            top: 100%;
                            right: 0;
                            background: var(--white);
                            min-width: 200px;
                            box-shadow: var(--shadow-lg);
                            border-radius: var(--border-radius);
                            padding: var(--spacing-2) 0;
                            opacity: 0;
                            visibility: hidden;
                            transform: translateY(-10px);
                            transition: var(--transition);
                            z-index: 100;
                        ">
                            <a href="<?php echo SITE_URL; ?>../pages/profile.php" style="
                                display: block;
                                padding: var(--spacing-2) var(--spacing-4);
                                color: var(--gray-700);
                                text-decoration: none;
                                transition: var(--transition);
                            ">
                                <i class="fas fa-user"></i>
                                Mon Profil
                            </a>
                            <a href="<?php echo SITE_URL; ?>../pages/favorites.php" style="
                                display: block;
                                padding: var(--spacing-2) var(--spacing-4);
                                color: var(--gray-700);
                                text-decoration: none;
                                transition: var(--transition);
                            ">
                                <i class="fas fa-heart"></i>
                                Mes Favoris
                            </a>
                            <hr style="margin: var(--spacing-2) 0; border: none; border-top: 1px solid var(--gray-200);">
                            <a href="<?php echo SITE_URL; ?>../includes/logout.php" style="
                                display: block;
                                padding: var(--spacing-2) var(--spacing-4);
                                color: var(--error-color);
                                text-decoration: none;
                                transition: var(--transition);
                            ">
                                <i class="fas fa-sign-out-alt"></i>
                                Déconnexion
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>../pages/login.php" class="action-btn" title="Se connecter">
                        <i class="fas fa-user"></i>
                        <span>Connexion</span>
                    </a>
                <?php endif; ?>
                
                <!-- Menu mobile -->
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

 <!-- Barre de recherche mobile -->
<div class="search-bar-mobile" id="searchBarMobile">
    <form action="<?php echo SITE_URL; ?>../pages/search.php" method="GET" class="search-form">
        <input type="text" name="q" placeholder="Rechercher des bijoux..." class="search-input">
        <button type="submit" class="search-btn">
            <i class="fas fa-search"></i>
        </button>
    </form>
</div>
</header>
<script>
window.isLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
</script>
<script>
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobileMenu');
    mobileMenu.classList.toggle('active');
    document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
}

function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    const isVisible = dropdown.style.opacity === '1';
    
    if (isVisible) {
        dropdown.style.opacity = '0';
        dropdown.style.visibility = 'hidden';
        dropdown.style.transform = 'translateY(-10px)';
    } else {
        dropdown.style.opacity = '1';
        dropdown.style.visibility = 'visible';
        dropdown.style.transform = 'translateY(0)';
    }
}

// Fermer le menu utilisateur en cliquant ailleurs
document.addEventListener('click', function(event) {
    const userMenu = document.querySelector('.user-menu');
    const dropdown = document.getElementById('userDropdown');
    
    if (userMenu && !userMenu.contains(event.target)) {
        dropdown.style.opacity = '0';
        dropdown.style.visibility = 'hidden';
        dropdown.style.transform = 'translateY(-10px)';
    }
});

// Fermer le menu mobile en cliquant sur l'overlay ou le bouton fermer
document.getElementById('mobileMenu').addEventListener('click', function(e) {
    if (e.target === this || e.target.classList.contains('mobile-menu-close') || e.target.closest('.mobile-menu-close')) {
        toggleMobileMenu();
    }
});

// Empêcher la propagation du clic dans le contenu du menu mobile
document.querySelector('.mobile-menu-content').addEventListener('click', function(e) {
    e.stopPropagation();
});

// Styles pour les liens du dropdown au survol
document.querySelectorAll('.user-dropdown a').forEach(link => {
    link.addEventListener('mouseenter', function() {
        this.style.backgroundColor = 'var(--gray-100)';
        this.style.color = 'var(--primary-color)';
    });
    
    link.addEventListener('mouseleave', function() {
        this.style.backgroundColor = 'transparent';
        if (!this.style.color.includes('var(--error-color)')) {
            this.style.color = 'var(--gray-700)';
        }
    });
});

// Toggle de la barre de recherche mobile
document.querySelector('.search-btn').addEventListener('click', function(e) {
    if (window.innerWidth <= 992) {
        e.preventDefault();
        const searchBar = document.querySelector('.search-bar');
        searchBar.classList.toggle('active');
        
        if (searchBar.classList.contains('active')) {
            searchBar.querySelector('input').focus();
        }
    }
});

// Fermer la barre de recherche si on clique ailleurs
document.addEventListener('click', function(e) {
    const searchBar = document.querySelector('.search-bar');
    if (searchBar.classList.contains('active') && 
        !e.target.closest('.search-bar') && 
        !e.target.closest('.mobile-menu-toggle')) {
        searchBar.classList.remove('active');
    }
});
const searchIconBtn = document.createElement('button');
searchIconBtn.className = 'search-icon-btn';
searchIconBtn.innerHTML = '<i class="fas fa-search"></i>';
searchIconBtn.onclick = function(e) {
    e.preventDefault();
    const searchBarMobile = document.getElementById('searchBarMobile');
    searchBarMobile.classList.toggle('active');
    
    // Fermer les autres menus ouverts
    document.getElementById('userDropdown').style.opacity = '0';
    document.getElementById('userDropdown').style.visibility = 'hidden';
    document.getElementById('userDropdown').style.transform = 'translateY(-10px)';
    
    if (document.getElementById('mobileMenu').classList.contains('active')) {
        toggleMobileMenu();
    }
};

// Ajoutez le bouton à la barre d'actions
const navbarActions = document.querySelector('.navbar-actions');
navbarActions.insertBefore(searchIconBtn, document.querySelector('.mobile-menu-toggle'));

// Fermer la barre de recherche si on clique ailleurs
document.addEventListener('click', function(e) {
    const searchBarMobile = document.getElementById('searchBarMobile');
    const searchIcon = document.querySelector('.search-icon-btn');
    
    if (searchBarMobile.classList.contains('active') && 
        !e.target.closest('.search-bar-mobile') && 
        !e.target.closest('.search-icon-btn') &&
        e.target !== searchIcon) {
        searchBarMobile.classList.remove('active');
    }
});
</script>

<style>
  
    /* Styles pour le menu mobile */
.mobile-menu-toggle {
    display: none;
    background: none;
    border: none;
    color: var(--gray-700);
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
}

#mobileMenu {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
}

#mobileMenu.active {
    opacity: 1;
    visibility: visible;
}

.mobile-menu-content {
    position: absolute;
    top: 0;
    right: 0;
    width: 80%;
    max-width: 300px;
    height: 100%;
    background-color: var(--white);
    padding: 1.5rem;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    overflow-y: auto;
}

#mobileMenu.active .mobile-menu-content {
    transform: translateX(0);
}

.mobile-menu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.mobile-menu-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
}

.mobile-nav {
    list-style: none;
    padding: 0;
    margin: 0;
}

.mobile-nav .nav-item {
    margin-bottom: 1rem;
}

.mobile-nav .nav-link {
    display: block;
    padding: 0.75rem 0;
    color: var(--gray-700);
    text-decoration: none;
    font-size: 1.1rem;
    transition: var(--transition);
}

.mobile-nav .nav-link:hover {
    color: var(--primary-color);
}

.mobile-search {
    margin: 1.5rem 0;
}

.mobile-search .search-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
}

.mobile-actions {
    margin-top: 2rem;
}

/* Nouveaux styles pour la barre de recherche mobile */
.search-icon-btn {
    display: none;
    background: none;
    border: none;
    color: var(--gray-700);
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0.5rem;
    margin-left: 0.5rem;
}

.search-bar-mobile {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    background: var(--white);
    padding: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 100;
    display: none;
}

.search-bar-mobile.active {
    display: block;
}

.search-bar-mobile .search-form {
    display: flex;
    width: 100%;
}

.search-bar-mobile .search-input {
    flex: 1;
    padding: 0.75rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius) 0 0 var(--border-radius);
    border-right: none;
}

.search-bar-mobile .search-btn {
    padding: 0 1rem;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 0 var(--border-radius) var(--border-radius) 0;
    cursor: pointer;
}

/* Responsive styles */
@media (max-width: 992px) {
    .navbar-nav,
    .search-bar {
        display: none;
    }
    
    .mobile-menu-toggle,
    .search-icon-btn {
        display: block;
    }
    
    .navbar-actions .action-btn span {
        display: none;
    }

    .navbar-actions {
        display: flex;
        align-items: center;
    }
}

@media (max-width: 576px) {
    .navbar-brand img {
        max-height: 40px;
    }
    
    #mobileMenu .user-dropdown {
        position: static;
        opacity: 1;
        visibility: visible;
        transform: none;
        box-shadow: none;
        padding: 0;
    }
}

</style>
