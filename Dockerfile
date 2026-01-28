# Utilisation de l'image officielle PHP avec Apache
FROM php:8.2-apache

# Installation des extensions PHP pour se connecter à MySQL plus tard
RUN docker-php-ext-install pdo pdo_mysql

#  copie tous les fichiers (index.html, style.css, main.js) dans le conteneur
COPY . /var/www/html/

#  s'assurer que le serveur a les bonnes permissions
RUN chown -R www-data:www-data /var/www/html