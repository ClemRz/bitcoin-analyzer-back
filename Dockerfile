# pull official base image
FROM php:7.3-apache

# update aptitude
RUN apt-get update

# install git
RUN apt-get install -y git

# install cron
RUN apt-get install -y cron

# install some extentions
RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN a2enmod rewrite

# install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN rm composer-setup.php

# copy the sources
COPY src/ /var/www/html/

# download dependencies
RUN composer install

# port exposure
EXPOSE 80