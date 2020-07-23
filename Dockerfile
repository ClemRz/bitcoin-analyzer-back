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

# copy the sources
COPY src/ /var/www/html/

# port exposure
EXPOSE 80