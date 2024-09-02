# Utiliser une image PHP avec Apache
FROM php:7.4-apache

# Installer les dépendances et les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_mysql

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Mettre à jour Composer
RUN composer self-update

# Copier les fichiers de l'application dans le conteneur
COPY . /var/www/html

# Créer le répertoire var si nécessaire
RUN mkdir -p /var/www/html/var

# Modifier les permissions
RUN chown -R www-data:www-data /var/www/html

# Exposer le port 80
EXPOSE 80
