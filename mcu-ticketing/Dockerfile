FROM php:8.2-apache

RUN a2enmod rewrite
RUN docker-php-ext-install pdo_mysql

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV PORT=8080

COPY apache.conf /etc/apache2/sites-available/000-default.conf
RUN sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf

COPY . /var/www/html/

EXPOSE 8080

CMD ["apache2-foreground"]
