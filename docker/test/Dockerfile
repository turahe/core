ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libsqlite3-dev \
    zip \
    unzip \
    netcat-openbsd \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    pdo_sqlite \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set build arguments for Laravel and Testbench versions
ARG LARAVEL_VERSION=^12.0
ARG TESTBENCH_VERSION=^10.0

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock* ./

# Install dependencies (including dev dependencies for testing)
RUN composer install --ignore-platform-reqs

# Copy application code
COPY . .

# Create test configuration
RUN cp phpunit.xml.dist phpunit.xml

# Copy and modify environment file for unit tests
COPY docker/database.env .env

# Set environment variables for unit tests (SQLite, no external services)
ENV DB_CONNECTION=sqlite
ENV DB_DATABASE=:memory:
ENV CACHE_DRIVER=array
ENV SESSION_DRIVER=array
ENV QUEUE_CONNECTION=sync
ENV APP_ENV=testing

# Set permissions
RUN chmod -R 755 /app

# Default command - run PHPUnit tests
CMD ["php", "vendor/bin/phpunit"] 