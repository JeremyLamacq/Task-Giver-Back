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

# Copier les fichiers de l'application dans le conteneur
COPY . /var/www/html

# Installer les dépendances PHP avec Composer
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader

# Créer le répertoire var si nécessaire
RUN mkdir -p /var/www/html/var

# Modifier les permissions
RUN chown -R www-data:www-data /var/www/html

# Configuration Apache
RUN a2enmod rewrite

# Exposer le port 80
EXPOSE 80

# Commande pour démarrer le serveur Apache en premier plan
CMD ["apache2-foreground"]
