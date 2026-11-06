FROM php:8.2-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]