<?php
/**
 * Barre de navigation pour les pages internes
 * Version simplifiée du header pour les sous-pages
 */
?>

<nav class="internal-navbar">
    <div class="container">
        <div class="navbar-content">
            <!-- Logo -->
            <div class="navbar-brand">
                <a href="<?php echo SITE_URL; ?>">
                    <h3>ElvyMade</h3>
                </a>
            </div>

            <!-- Fil d'Ariane -->
            <div class="breadcrumb">
                <?php if (isset($breadcrumb) && is_array($breadcrumb)): ?>
                    <?php foreach ($breadcrumb as $index => $item): ?>
                        <?php if ($index > 0): ?>
                            <span class="breadcrumb-separator">/</span>
                        <?php endif; ?>
                        
                        <?php if (isset($item['url']) && $index < count($breadcrumb) - 1): ?>
                            <a href="<?php echo $item['url']; ?>" class="breadcrumb-link">
                                <?php echo $item['title']; ?>
                            </a>
                        <?php else: ?>
                            <span class="breadcrumb-current">
                                <?php echo $item['title']; ?>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Actions rapides -->
            <div class="navbar-actions">
                <a href="<?php echo SITE_URL; ?>" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
                
                <?php if (isLoggedIn()): ?>
                    <a href="profile.php" class="user-btn">
                        <i class="fas fa-user"></i>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="login-btn">
                        Connexion
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
