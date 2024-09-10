# Utiliser PHP 8.2 FPM comme image de base
FROM php:8.2-fpm

# Installer les dépendances nécessaires (gosu, nginx et git)
RUN apt-get update && apt-get install -y \
    nginx git curl \
    && curl -o /usr/local/bin/gosu -SL 'https://github.com/tianon/gosu/releases/download/1.14/gosu-amd64' \
    && chmod +x /usr/local/bin/gosu \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copier les fichiers de l'application dans le conteneur
COPY . /var/www/html

# Copier la configuration Nginx dans le conteneur
COPY docker/nginx/nginx-site.conf /etc/nginx/conf.d/default.conf

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Préparer les répertoires et les permissions pour la production
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var/cache var/log \
    && gosu www-data composer install --no-dev --optimize-autoloader \
    && gosu www-data php bin/console cache:clear --env=prod \
    && gosu www-data php bin/console assets:install --symlink \
    && gosu www-data php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# Exposer le port 80 pour Nginx
EXPOSE 80

# Commande pour démarrer Nginx et PHP-FPM
CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
