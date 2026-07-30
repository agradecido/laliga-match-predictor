FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
        libzip-dev \
        libsqlite3-dev \
        libicu-dev \
        libpng-dev \
        libonig-dev \
    && docker-php-ext-install pdo pdo_sqlite zip bcmath pcntl intl gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY .devcontainer/php/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY .devcontainer/php/wait-entrypoint.sh /usr/local/bin/wait-entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/wait-entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
