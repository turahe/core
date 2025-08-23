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
    zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Xdebug for coverage testing
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first for better caching
COPY composer.json composer.lock* ./

# Install dependencies with retry mechanism and better timeout
RUN composer install --ignore-platform-reqs --no-interaction --prefer-dist --optimize-autoloader \
    --timeout=600 --retry=3

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
ENV XDEBUG_MODE=coverage
ENV XDEBUG_CONFIG="client_host=host.docker.internal"

# Set permissions
RUN chmod -R 755 /app

# Default command - run PHPUnit tests
CMD ["php", "vendor/bin/phpunit"] 