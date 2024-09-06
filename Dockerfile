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

# Passer à l'utilisateur non-root
USER appuser

# Vérifier la version de Composer
RUN composer --version

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application dans le conteneur
COPY --chown=appuser:appuser . .

# Installer les dépendances PHP
RUN composer install --optimize-autoloader

# Exposer le port 9000 pour PHP-FPM
EXPOSE 9000

# Commande pour démarrer PHP-FPM
CMD ["php-fpm"]
