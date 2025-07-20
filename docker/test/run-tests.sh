#!/bin/bash

# Turahe Core Test Runner
# This script runs tests in the Docker environment

set -e

echo "🧪 Turahe Core Test Runner"
echo "=========================="
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

print_status "Starting test environment..."

# Start required services
print_status "Starting MySQL and Redis..."
docker-compose up -d mysql redis

# Wait for services to be ready
print_status "Waiting for services to be ready..."
sleep 10

# Check if MySQL is ready
print_status "Checking MySQL connection..."
until docker exec turahe-core-mysql mysqladmin ping -h"localhost" --silent; do
    print_warning "MySQL is not ready yet. Waiting..."
    sleep 2
done
print_success "MySQL is ready!"

# Check if Redis is ready
print_status "Checking Redis connection..."
until docker exec turahe-core-redis redis-cli ping > /dev/null 2>&1; do
    print_warning "Redis is not ready yet. Waiting..."
    sleep 2
done
print_success "Redis is ready!"

# Build and run the test container
print_status "Building test container..."
docker-compose build app

print_status "Running tests..."
docker-compose run --rm app

# Check test results
if [ $? -eq 0 ]; then
    print_success "All tests passed! 🎉"
else
    print_error "Some tests failed! ❌"
    exit 1
fi

print_status "Cleaning up..."
docker-compose down

print_success "Test run completed!" 