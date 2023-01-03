FROM php:7.4.2-apache as build
WORKDIR /var/www/html
COPY . .
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        zlib1g-dev \
        libxml2-dev \
        libzip-dev \
        graphviz \
    && docker-php-ext-configure gd \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install zip \
    && docker-php-ext-install xml

RUN composer install
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
#COPY .env.example /var/www/html/.env

RUN chmod 777 -R /var/www/html/storage/ && \
    chown -R www-data:www-data /var/www/ && \
    a2enmod rewrite
EXPOSE 80
CMD ["apachectl", "-D", "FOREGROUND"]