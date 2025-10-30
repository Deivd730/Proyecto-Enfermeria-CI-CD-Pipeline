FROM php:8.2-cli

# Install system dependencies needed for composer and general tooling
RUN apt-get update \
    && apt-get install -y git unzip zip curl --no-install-recommends \
    && rm -rf /var/lib/apt/lists/*

# Copy composer binary from official composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

# Install PHP dependencies (production)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

EXPOSE 8000

# Simple PHP built-in server for this example container
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
