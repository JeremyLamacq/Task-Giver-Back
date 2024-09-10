# Utiliser PHP 8.2 FPM comme image de base
FROM php:8.2-fpm

# Installer Nginx et Git
RUN apt-get update && apt-get install -y nginx git

# Copier les fichiers de l'application dans le conteneur
COPY . /var/www/html

# Copier la configuration Nginx dans le conteneur
COPY docker/nginx/nginx-site.conf /etc/nginx/conf.d/default.conf

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Préparer les répertoires et les permissions pour la production
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var/cache var/log \
    && su www-data -c "composer install --no-dev --optimize-autoloader" \
    && su www-data -c "php bin/console cache:clear --env=prod" \
    && su www-data -c "php bin/console assets:install --symlink" \
    && su www-data -c "php bin/console doctrine:migrations:migrate --no-interaction --env=prod"

# Exposer le port 80 pour Nginx
EXPOSE 80

# Commande pour démarrer Nginx et PHP-FPM
CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
