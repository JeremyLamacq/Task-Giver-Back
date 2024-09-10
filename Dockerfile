FROM richarvey/nginx-php-fpm:1.7.4

COPY . /var/www/html

# Image config
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Symfony config
ENV APP_ENV production
ENV APP_DEBUG false

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

# Mettre à jour Composer
RUN composer self-update

# Vérifiez les informations sur les dépendances
RUN composer diagnose \
    && composer show --platform

# Préparer les répertoires et les permissions pour la production
RUN mkdir -p var/cache var/log \
    && composer install --optimize-autoloader \
    && php bin/console cache:clear --env=prod \
    && php bin/console assets:install --symlink \
    && php bin/console doctrine:migrations:migrate --no-interaction --env=prod

CMD ["/start.sh"]
