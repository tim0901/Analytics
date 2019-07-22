FROM php:7.1-apache
RUN apt-get update
RUN apt-get install unzip
RUN apt-get install zip
RUN apt-get install -y libcurl4-openssl-dev pkg-config libssl-dev
RUN docker-php-ext-install mysqli
RUN pecl install xdebug-2.6.0
RUN docker-php-ext-enable xdebug
RUN echo "xdebug.remote_enable=1" >> /usr/local/etc/php/php.ini
COPY --from=composer /usr/bin/composer /usr/bin/composer
