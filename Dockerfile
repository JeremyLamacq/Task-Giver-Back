# Utiliser une image PHP avec Apache
FROM php:7.4-apache

# Installer les dépendances et les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_mysql

# Mettre à jour Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer self-update

# Copier les fichiers de l'application dans le conteneur
COPY . /var/www/html

# Modifier les permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/var

# Réinstaller les dépendances PHP avec Composer
RUN composer install --optimize-autoloader

# Configuration Apache
RUN a2enmod rewrite

# Exposer le port 80
EXPOSE 80
