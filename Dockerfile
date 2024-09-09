# Utiliser une image PHP-FPM avec PHP 8.1
FROM php:8.1-fpm

# Installer les dépendances système nécessaires
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpq-dev \
    unzip \
    git \
    && docker-php-ext-install intl pdo pdo_pgsql zip

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application dans le conteneur
COPY . .

# Configurer les variables d'environnement
ENV APP_ENV=production
ENV APP_DEBUG=false

# Préparer les répertoires et les permissions pour la production
RUN mkdir -p var/cache var/log \
    && composer install --no-dev --optimize-autoloader \
    && php bin/console cache:clear --env=prod \
    && php bin/console assets:install --symlink \
    && php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# Exposer le port 80 pour PHP-FPM
EXPOSE 80

# Commande pour démarrer PHP-FPM
CMD ["php-fpm"]
