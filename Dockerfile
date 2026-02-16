FROM php:8.2-apache

# Installation des extensions pour MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Installation des dépendances pour MongoDB
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev pkg-config libssl-dev \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# On active le module de réécriture d'Apache (utile pour les redirections)
RUN a2enmod rewrite

# On donne les droits au serveur sur les fichiers
RUN chown -R www-data:www-data /var/www/html