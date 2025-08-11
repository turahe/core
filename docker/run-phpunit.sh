#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}[INFO]${NC} Running PHPUnit tests with Docker..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}[ERROR]${NC} Docker is not running. Please start Docker and try again."
    exit 1
fi

# Build the test image
echo -e "${BLUE}[INFO]${NC} Building test image..."
if ! docker build -f docker/test/Dockerfile -t turahe-core-test .; then
    echo -e "${RED}[ERROR]${NC} Failed to build test image"
    exit 1
fi

# Run tests
echo -e "${BLUE}[INFO]${NC} Running tests..."
if docker run --rm turahe-core-test php vendor/bin/phpunit "$@"; then
    echo -e "${GREEN}[SUCCESS]${NC} Tests completed successfully!"
    exit_code=0
else
    echo -e "${RED}[ERROR]${NC} Tests failed!"
    exit_code=1
fi

# Clean up image
echo -e "${BLUE}[INFO]${NC} Cleaning up test image..."
docker rmi turahe-core-test > /dev/null 2>&1

# Stop any running containers
echo -e "${BLUE}[INFO]${NC} Stopping any running containers..."
if [ -f "docker-compose.yml" ]; then
    if command -v docker-compose &> /dev/null; then
        docker-compose down > /dev/null 2>&1
    elif docker compose version &> /dev/null; then
        docker compose down > /dev/null 2>&1
    fi
    echo -e "${GREEN}[SUCCESS]${NC} Containers stopped"
fi

exit $exit_code
