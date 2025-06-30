<?php
/**
 * Pied de page du site ElvyMade
 */
?>

<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <!-- Informations de l'entreprise -->
            <div class="footer-section">
                <h3>ElvyMade</h3>
                <p>Votre marketplace de confiance au Cameroun pour tous vos achats en ligne.</p>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <!-- Liens rapides -->
            <div class="footer-section">
                <h4>Liens Rapides</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>">Accueil</a></li>
                    <li><a href="../pages/search.php">Produits</a></li>
                    <li><a href="../pages/category.php">Catégories</a></li>
                 
                </ul>
            </div>


            <!-- Contact -->
            <div class="footer-section">
                <h4>Contact</h4>
                <div class="contact-info">
                    <p><i class="fas fa-envelope"></i> <?php echo SITE_EMAIL; ?></p>
                    <p><i class="fas fa-phone"></i> +237 6 96 09 58 05 </p>
                    <p><i class="fas fa-map-marker-alt"></i> Douala, Cameroun</p>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <?php echo date('Y'); ?> ElvyMade. Tous droits réservés.</p>
                <div class="footer-bottom-links">
                    <a href="#">Politique de confidentialité</a>
                    <a href="#">Conditions d'utilisation</a>
                </div>
            </div>
        </div>
    </div>
</footer>
