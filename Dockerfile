# Image PHP avec extensions nécessaires
FROM php:8.2-fpm

# Installer dépendances système
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip git curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Installer Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Créer dossier de travail
WORKDIR /var/www

# Copier les fichiers Laravel
COPY . .

# Installer dépendances Laravel (dans le conteneur, avec PHP 8.2)
RUN composer install --optimize-autoloader

# Donner les bons droits
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Exposer le port interne PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
