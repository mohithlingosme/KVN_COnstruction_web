FROM php:8.2-apache

WORKDIR /var/www/html

RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite headers expires

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-kvn.ini
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY . /var/www/html

RUN mkdir -p storage/logs uploads public/uploads \
    && chown -R www-data:www-data storage uploads public/uploads

EXPOSE 80

CMD ["apache2-foreground"]
