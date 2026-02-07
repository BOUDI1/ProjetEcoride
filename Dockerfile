FROM php:8.2-apache
# Installation des extensions PDO pour MySQL
RUN docker-php-ext-install pdo pdo_mysql
# Activation du module rewrite d'Apache (utile pour le déploiement)
RUN a2enmod rewrite
COPY . /var/www/html/
EXPOSE 80