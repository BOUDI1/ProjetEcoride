# On utilise une image PHP officielle avec Apache
FROM php:8.2-apache

# Mise à jour du système et installation des dépendances nécessaires
RUN apt-get update && apt-get install -y \
    libssl-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# 1. Installation de l'extension PDO MySQL (pour l'Activité 2 - SQL)
RUN docker-php-ext-install pdo_mysql

# 2. Installation de l'extension MongoDB via PECL (pour l'Activité 2 - NoSQL)
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Activation du module de réécriture d'Apache (nécessaire pour le .htaccess)
RUN a2enmod rewrite

# On définit le dossier de travail dans le conteneur
WORKDIR /var/www/html

# On s'assure que les permissions sont correctes
RUN chown -R www-data:www-data /var/www/html