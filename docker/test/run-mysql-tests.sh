#!/bin/bash

# MySQL Test Runner for Turahe Core
# This script runs tests using MySQL in Docker environment

set -e

echo "🧪 MySQL Test Runner for Turahe Core"
echo "===================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    print_error "Docker is not running. Please start Docker and try again."
    exit 1
fi

# Check if docker-compose is available
if ! command -v docker-compose &> /dev/null; then
    print_error "docker-compose is not installed. Please install docker-compose and try again."
    exit 1
fi

print_status "Starting MySQL test environment..."

# Start MySQL service
print_status "Starting MySQL..."
docker-compose up -d mysql

# Wait for MySQL to be ready
print_status "Waiting for MySQL to be ready..."
sleep 10

# Check if MySQL is ready
print_status "Checking MySQL connection..."
until docker exec turahe-core-mysql mysqladmin ping -h"localhost" --silent; do
    print_warning "MySQL is not ready yet. Waiting..."
    sleep 2
done
print_success "MySQL is ready!"

# Create test database if it doesn't exist
print_status "Setting up test database..."
docker exec turahe-core-mysql mysql -u turahe -pturahe123 -e "CREATE DATABASE IF NOT EXISTS turahe_core_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
print_success "Test database ready!"

# Set environment variables for testing
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_DATABASE=turahe_core_testing
export DB_USERNAME=turahe
export DB_PASSWORD=turahe123
export DB_CONNECTION=mysql

export REDIS_HOST=127.0.0.1
export REDIS_PORT=6379
export REDIS_DB=1
export REDIS_PASSWORD=null

export CORE_TABLE_USE_TIMESTAMPS=false
export USERSTAMPS_USERS_TABLE_COLUMN_TYPE=ulid
export APP_KEY=base64:MFOsOH9RomiI2LRdgP4hIeoQJ5nyBhdABdH77UY2zi8=

# Core table names
export CORE_TABLE_SETTINGS=settings
export CORE_TABLE_ORGANIZATIONS=organizations
export CORE_TABLE_MODEL_HAS_ORGANIZATION=model_has_organization
export CORE_TABLE_TAXONOMIES=taxonomies
export CORE_TABLE_MODEL_HAS_TAXONOMIES=model_has_taxonomies
export CORE_TABLE_TAGS=tags
export CORE_TABLE_TAGGABLES=taggables
export CORE_TABLE_OAUTH_ACCOUNTS=oauth_accounts

# Cache configuration
export CORE_CACHE_ENABLED=true
export CORE_CACHE_SETTINGS_TTL=3600

print_status "Environment variables set for MySQL testing"

# Run migrations for testing
print_status "Running database migrations..."
php artisan migrate --env=testing --force

# Run tests
print_status "Running PHPUnit tests with MySQL..."
php vendor/bin/phpunit

# Check test results
if [ $? -eq 0 ]; then
    print_success "All tests passed! 🎉"
else
    print_error "Some tests failed! ❌"
    exit 1
fi

print_status "Cleaning up..."
docker-compose down

print_success "MySQL test run completed!" 