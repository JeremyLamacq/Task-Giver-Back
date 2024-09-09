# Utiliser l'image PHP 8.2 FPM comme base
FROM php:8.2.13-fpm

# Installer les dépendances système nécessaires
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpq-dev \
    unzip \
    git \
    && docker-php-ext-install intl pdo pdo_pgsql zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Ajouter un utilisateur non-root
RUN useradd -ms /bin/sh -u 1000 appuser

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application dans le conteneur
COPY --chown=appuser:appuser . .

# Passer à l'utilisateur non-root
USER appuser

# Installer les dépendances PHP
RUN composer install --optimize-autoloader

# Ajouter une configuration PHP-FPM pour écouter sur le port 80
COPY ./docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/www.conf

# Préparer les répertoires et les permissions pour la production
RUN mkdir -p var/cache var/log; \
    composer dump-autoload --classmap-authoritative; \
    composer dump-env prod; \
    composer run-script post-install-cmd; \
    chmod +x bin/console; sync;

# Exposer le port 80 pour PHP-FPM
EXPOSE 80

# Commande pour démarrer PHP-FPM
CMD ["php-fpm"]