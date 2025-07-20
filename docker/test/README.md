# Docker Testing Environment

This directory contains the Docker setup for testing the Turahe Core package with MySQL and Redis.

## Overview

The Docker testing environment provides:
- **MySQL 8.0** database for testing
- **Redis** for caching tests
- **PHP 8.3+** with all required extensions
- **Laravel Testbench** for package testing
- **Isolated environment** for consistent testing

## Quick Start

### Prerequisites
- Docker
- Docker Compose

### Running Tests Locally

1. **Start the services:**
   ```bash
   docker-compose up -d mysql redis
   ```

2. **Run tests with Docker:**
   ```bash
   # Linux/Mac
   ./docker/test/run-mysql-tests.sh
   
   # Windows
   docker/test/run-mysql-tests.bat
   ```

3. **Or run manually:**
   ```bash
   # Build test container
   docker build -f docker/test/Dockerfile -t turahe-core-test .
   
   # Run tests
   docker run --rm --network host turahe-core-test vendor/bin/phpunit
   ```

## Configuration

### Environment Variables

The test environment uses these environment variables:

```bash
# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=turahe_core_testing
DB_USERNAME=turahe
DB_PASSWORD=turahe123
DB_CONNECTION=mysql

# Core Configuration
CORE_TABLE_USE_TIMESTAMPS=false
USERSTAMPS_USERS_TABLE_COLUMN_TYPE=ulid
APP_KEY=base64:MFOsOH9RomiI2LRdgP4hIeoQJ5nyBhdABdH77UY2zi8=

# Table Names
CORE_TABLE_SETTINGS=settings
CORE_TABLE_ORGANIZATIONS=organizations
CORE_TABLE_MODEL_HAS_ORGANIZATION=model_has_organization
CORE_TABLE_TAXONOMIES=taxonomies
CORE_TABLE_MODEL_HAS_TAXONOMIES=model_has_taxonomies
CORE_TABLE_TAGS=tags
CORE_TABLE_TAGGABLES=taggables
CORE_TABLE_OAUTH_ACCOUNTS=oauth_accounts

# Cache Configuration
CORE_CACHE_ENABLED=true
CORE_CACHE_SETTINGS_TTL=3600

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DB=1
REDIS_PASSWORD=null
```

### Docker Services

- **MySQL**: `mysql:8.0` on port 3306
- **Redis**: `redis:7-alpine` on port 6379
- **phpMyAdmin**: `phpmyadmin/phpmyadmin` on port 8080
- **Redis Commander**: `rediscommander/redis-commander` on port 8081
- **ImgProxy**: `darthsim/imgproxy` on port 8082

## GitHub Actions CI

The package includes GitHub Actions workflows for Docker-based testing:

### Workflows

1. **`ci-docker-simple.yml`** - Simple Docker CI with PHP 8.4 + Laravel 10
2. **`ci-docker.yml`** - Full matrix testing with multiple PHP/Laravel versions

### CI Features

- **Matrix Testing**: Multiple PHP and Laravel versions
- **Docker Services**: MySQL + Redis in CI environment
- **Code Quality**: PHPStan, Psalm, PHP CS Fixer
- **Package Testing**: Installation and integration tests
- **Coverage Reports**: Codecov integration

### CI Jobs

- **Docker Tests**: Run PHPUnit tests in Docker containers
- **Code Quality**: Static analysis and code style checks
- **Security**: Security vulnerability scanning
- **Package Test**: Test package installation in fresh Laravel project
- **Lint**: PHP syntax validation

## Dockerfile

The `Dockerfile` supports build arguments for different PHP and Laravel versions:

```dockerfile
ARG PHP_VERSION=8.3
ARG LARAVEL_VERSION=10.*
ARG TESTBENCH_VERSION=8.*
```

### Building with Different Versions

```bash
# PHP 8.4 + Laravel 10
docker build -f docker/test/Dockerfile \
  --build-arg PHP_VERSION=8.4 \
  --build-arg LARAVEL_VERSION=10.* \
  --build-arg TESTBENCH_VERSION=8.* \
  -t turahe-core-test .

# PHP 8.2 + Laravel 9
docker build -f docker/test/Dockerfile \
  --build-arg PHP_VERSION=8.2 \
  --build-arg LARAVEL_VERSION=9.* \
  --build-arg TESTBENCH_VERSION=7.* \
  -t turahe-core-test .
```

## Testing Features

### Database Testing
- Uses MySQL instead of SQLite for more realistic testing
- Supports foreign key constraints and complex relationships
- Tests with actual database migrations

### Cache Testing
- Redis integration for settings caching
- Tests cache invalidation and TTL
- Configurable cache settings

### Package Testing
- Tests package installation in fresh Laravel projects
- Validates service provider registration
- Tests migration publishing and execution

## Troubleshooting

### Common Issues

1. **Port conflicts**: Ensure ports 3306, 6379, 8080-8082 are available
2. **Permission issues**: Run Docker commands with appropriate permissions
3. **Network issues**: Use `--network host` for container communication

### Debugging

```bash
# Check service logs
docker-compose logs mysql redis

# Access MySQL directly
docker-compose exec mysql mysql -u turahe -pturahe123

# Access Redis directly
docker-compose exec redis redis-cli

# Run tests with verbose output
docker run --rm --network host turahe-core-test vendor/bin/phpunit --verbose
```

## Development

### Adding New Tests

1. Create test files in `tests/` directory
2. Use the `TestCase` class for database testing
3. Tests automatically use MySQL and Redis from Docker

### Modifying Docker Setup

1. Update `docker-compose.yml` for new services
2. Modify `Dockerfile` for new PHP extensions or tools
3. Update test scripts for new environment variables

### Local Development

```bash
# Start services for development
docker-compose up -d

# Run specific test
docker run --rm --network host turahe-core-test vendor/bin/phpunit --filter="test_organization_creation"

# Run with coverage
docker run --rm --network host turahe-core-test vendor/bin/phpunit --coverage-html coverage
``` 