FROM php:8.2-apache

RUN a2enmod rewrite
RUN apt-get update && apt-get install -y libzip-dev unzip \
    && docker-php-ext-install pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV PORT=8080

COPY apache.conf /etc/apache2/sites-available/000-default.conf
RUN sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /var/www/html/
RUN cd /var/www/html && composer install --no-dev --no-interaction

EXPOSE 8080

CMD ["apache2-foreground"]
