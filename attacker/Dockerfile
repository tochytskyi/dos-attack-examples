FROM php:8.0-cli-alpine

RUN mkdir /usr/scripts
COPY ./scripts /usr/scripts
RUN chmod -R +x /usr/scripts

RUN docker-php-ext-install pcntl

#COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
#RUN composer install

#CMD [ "php", "./run.php" ]