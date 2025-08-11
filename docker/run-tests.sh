#!/bin/bash

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

# Function to check if Docker is running
check_docker() {
    if ! docker info > /dev/null 2>&1; then
        print_error "Docker is not running. Please start Docker and try again."
        exit 1
    fi
    print_success "Docker is running"
}

# Function to check if Docker Compose is available
check_docker_compose() {
    if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
        print_error "Docker Compose is not available. Please install Docker Compose and try again."
        exit 1
    fi
    print_success "Docker Compose is available"
}

# Function to get docker-compose command
get_docker_compose_cmd() {
    if command -v docker-compose &> /dev/null; then
        echo "docker-compose"
    else
        echo "docker compose"
    fi
}

# Function to stop and remove containers
cleanup() {
    print_status "Cleaning up containers..."
    local compose_cmd=$(get_docker_compose_cmd)
    $compose_cmd down --remove-orphans
    print_success "Cleanup completed"
}

# Function to handle script interruption
trap cleanup INT TERM

# Main script
main() {
    print_status "Starting test environment setup..."
    
    # Check prerequisites
    check_docker
    check_docker_compose
    
    # Get docker-compose command
    local compose_cmd=$(get_docker_compose_cmd)
    
    # Stop any existing containers
    print_status "Stopping existing containers..."
    $compose_cmd down --remove-orphans
    
    # Build and start services
    print_status "Building and starting services..."
    $compose_cmd up --build -d mysql redis
    
    # Wait for services to be ready
    print_status "Waiting for MySQL to be ready..."
    while ! docker exec turahe-core-mysql mysqladmin ping -h"localhost" --silent; do
        sleep 2
    done
    print_success "MySQL is ready"
    
    print_status "Waiting for Redis to be ready..."
    while ! docker exec turahe-core-redis redis-cli ping > /dev/null 2>&1; do
        sleep 2
    done
    print_success "Redis is ready"
    
    # Create test database
    print_status "Creating test database..."
    docker exec turahe-core-mysql mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS turahe_core_testing;"
    print_success "Test database created"
    
    # Run tests
    print_status "Running tests..."
    if $compose_cmd run --rm app php vendor/bin/phpunit "$@"; then
        print_success "Tests completed successfully!"
        exit_code=0
    else
        print_error "Tests failed!"
        exit_code=1
    fi
    
    # Cleanup
    cleanup
    
    exit $exit_code
}

# Parse command line arguments
if [[ "$1" == "--help" || "$1" == "-h" ]]; then
    echo "Usage: $0 [phpunit-options]"
    echo ""
    echo "Options:"
    echo "  --help, -h     Show this help message"
    echo "  --filter       Filter tests by name"
    echo "  --testsuite    Run specific test suite"
    echo "  --stop-on-failure  Stop on first failure"
    echo ""
    echo "Examples:"
    echo "  $0                           # Run all tests"
    echo "  $0 --filter=UserModelTest   # Run specific test"
    echo "  $0 --testsuite=default      # Run default test suite"
    echo ""
    exit 0
fi

# Run main function
main "$@"
