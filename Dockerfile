# Utilise une image PHP officielle avec Apache
FROM php:8.1-apache

# Active les extensions PHP dont tu pourrais avoir besoin
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Active le module de réécriture d'URL d'Apache
RUN a2enmod rewrite

# Configure Apache pour servir depuis le dossier public (si tu as une structure avec /public)
# Si ton index.php est à la racine, remplace "public" par "."
RUN sed -i 's|/var/www/html|/var/www/html/.|g' /etc/apache2/sites-available/000-default.conf

# Copie tous tes fichiers dans le conteneur
COPY . /var/www/html/

# Définit les permissions (optionnel mais recommandé)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage 2>/dev/null || true

# Expose le port 80
EXPOSE 80

# Démarre Apache
CMD ["apache2-foreground"]