# Docker Test Scripts

This directory contains scripts to run tests using Docker containers for the Turahe Core package.

## Available Scripts

### Simple PHPUnit Scripts (Recommended)
- `run-phpunit.sh` - Bash script for Unix-like systems (uses Dockerfile directly)
- `run-phpunit.bat` - Batch script for Windows systems (uses Dockerfile directly)
- `run-phpunit.ps1` - PowerShell script for Windows systems (uses Dockerfile directly)

### Full Docker Compose Scripts
- `run-tests.sh` - Bash script for Unix-like systems (full environment setup)
- `run-tests.bat` - Batch script for Windows systems (full environment setup)
- `run-tests.ps1` - PowerShell script for Windows systems (full environment setup)

## Prerequisites

- Docker Desktop installed and running
- Docker Compose available (either `docker-compose` or `docker compose`)

## Usage

### Basic Usage

#### Simple PHPUnit Scripts (Recommended)
```bash
# Linux/macOS
chmod +x docker/run-phpunit.sh
./docker/run-phpunit.sh
./docker/run-phpunit.sh --filter=UserModelTest

# Windows (Batch)
docker\run-phpunit.bat
docker\run-phpunit.bat --filter=UserModelTest

# Windows (PowerShell)
docker\run-phpunit.ps1
docker\run-phpunit.ps1 --filter=UserModelTest

# Run specific test suites
./docker/run-phpunit.sh --testsuite=unit
./docker/run-phpunit.sh --testsuite=feature
```

#### Full Docker Compose Scripts
```bash
# Linux/macOS
chmod +x docker/run-tests.sh
./docker/run-tests.sh
./docker/run-tests.sh --filter=UserModelTest

# Windows (Batch)
docker\run-tests.bat
docker\run-tests.bat --filter=UserModelTest

# Windows (PowerShell)
docker\run-tests.ps1
docker\run-tests.ps1 --filter=UserModelTest

# Run specific test suites
./docker/run-tests.sh --testsuite=unit
./docker/run-tests.sh --testsuite=feature
```

### Command Line Options

Both scripts accept all standard PHPUnit command line arguments:

- `--filter=<pattern>` - Filter tests by name
- `--testsuite=<name>` - Run specific test suite
- `--stop-on-failure` - Stop on first failure
- `--verbose` - Verbose output
- `--coverage-html=<dir>` - Generate HTML coverage report
- `--help` or `-h` - Show help information

### Test Suites

The project includes three test suites:

- **`default`** - Runs all tests (equivalent to running without `--testsuite`)
- **`unit`** - Runs only unit tests from `tests/Unit/` directory
- **`feature`** - Runs only feature/integration tests from `tests/Feature/` directory

## Script Types

### Simple PHPUnit Scripts (`run-phpunit.*`)
These scripts use the Dockerfile directly to build a test image and run PHPUnit:
- **Pros**: Simple, fast, no external dependencies
- **Cons**: No database/Redis services, limited to unit tests
- **Use case**: Quick unit test runs, CI/CD pipelines

### Full Docker Compose Scripts (`run-tests.*`)
These scripts set up the complete test environment:
- **Pros**: Full database and Redis support, integration tests
- **Cons**: More complex, slower startup
- **Use case**: Full test suite, integration tests

### PowerShell Scripts (`.ps1`)
PowerShell scripts provide enhanced features over batch scripts:
- **Pros**: Better error handling, cross-platform compatibility, advanced parameter parsing
- **Cons**: Requires PowerShell 5.1+ or PowerShell Core
- **Use case**: Windows environments, advanced scripting needs

## What the Scripts Do

### Simple PHPUnit Scripts
1. **Build** - Create test image from Dockerfile
2. **Execute** - Run PHPUnit in the container
3. **Cleanup** - Remove the test image and stop any running containers

### Full Docker Compose Scripts
1. **Prerequisites Check**
   - Verify Docker is running
   - Check Docker Compose availability
   - Detect the correct docker-compose command

2. **Environment Setup**
   - Stop any existing containers
   - Build and start MySQL and Redis services
   - Wait for services to be ready
   - Create test database (`turahe_core_testing`)

3. **Test Execution**
   - Run tests using the app container
   - Pass through all PHPUnit arguments
   - Capture exit code for success/failure

4. **Cleanup**
   - Stop and remove all containers
   - Clean up networks and volumes

## Automatic Cleanup

All scripts automatically clean up after test execution:

- **Test Images**: Removed after use to save disk space
- **Containers**: All running containers are stopped
- **Networks**: Docker networks are cleaned up
- **Volumes**: Volumes are removed (when using `--remove-orphans`)

This ensures a clean environment for subsequent test runs and prevents resource accumulation.

## Test Suite Organization

The project follows Laravel's standard test organization:

### Unit Tests (`tests/Unit/`)
- **Purpose**: Test individual classes, methods, and functions in isolation
- **Database**: Usually mocked or use in-memory SQLite
- **Speed**: Fast execution, no external dependencies
- **Use case**: Testing business logic, models, services, utilities

### Feature Tests (`tests/Feature/`)
- **Purpose**: Test complete features and workflows
- **Database**: Real database connections (MySQL in Docker)
- **Speed**: Slower execution, requires full environment
- **Use case**: Testing API endpoints, database interactions, full user flows

### Running Test Suites

```bash
# Run only unit tests (fast, no database needed)
docker\run-phpunit.ps1 --testsuite=unit

# Run only feature tests (slower, requires database)
docker\run-phpunit.ps1 --testsuite=feature

# Run all tests
docker\run-phpunit.ps1
```

## Test Environment

The scripts use the following Docker services:

- **MySQL 8.0** - Database for testing
- **Redis 7** - Cache and session storage
- **App Container** - PHP 8.4 with all required extensions

### Environment Variables

The test environment is configured with:

```env
APP_ENV=testing
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=turahe_core_testing
DB_USERNAME=turahe
DB_PASSWORD=turahe123
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_DB=1
```

## Troubleshooting

### Common Issues

1. **Docker not running**
   - Start Docker Desktop
   - Ensure Docker service is running

2. **Port conflicts**
   - The scripts use standard ports (3306 for MySQL, 6379 for Redis)
   - Stop any local services using these ports

3. **Permission denied (Linux/macOS)**
   - Make the script executable: `chmod +x docker/run-tests.sh`

4. **Tests fail to connect to database**
   - Wait for services to fully start
   - Check container logs: `docker logs turahe-core-mysql`

### Debug Mode

To see more detailed output, you can modify the scripts to add `--verbose` flags to the docker-compose commands.

## Integration with CI/CD

These scripts can be easily integrated into CI/CD pipelines:

```yaml
# GitHub Actions example
- name: Run Unit Tests
  run: ./docker/run-phpunit.sh --testsuite=unit

- name: Run Feature Tests
  run: ./docker/run-tests.sh --testsuite=feature

# GitLab CI example
test:
  script:
    - ./docker/run-phpunit.sh --testsuite=unit
    - ./docker/run-tests.sh --testsuite=feature
```

## Common Testing Scenarios

### Development Workflow
```bash
# Quick unit test during development
docker\run-phpunit.ps1 --testsuite=unit

# Full test run before commit
docker\run-phpunit.ps1

# Integration tests for database changes
docker\run-tests.ps1 --testsuite=feature
```

### CI/CD Pipeline
```bash
# Fast feedback - unit tests only
docker\run-phpunit.ps1 --testsuite=unit

# Comprehensive testing - all tests with full environment
docker\run-tests.ps1
```

### Debugging Specific Tests
```bash
# Run specific test class
docker\run-phpunit.ps1 --filter=UserModelTest

# Run specific test method
docker\run-phpunit.ps1 --filter=test_user_can_be_created

# Run tests with verbose output
docker\run-phpunit.ps1 --verbose --testsuite=unit
```

## Customization

You can modify the scripts to:

- Change service names or ports
- Add additional services (e.g., PostgreSQL, MongoDB)
- Modify PHPUnit configuration
- Add custom environment variables
- Implement parallel test execution

## Support

For issues with the test scripts, check:

1. Docker and Docker Compose versions
2. Container logs: `docker logs <container-name>`
3. Network connectivity between containers
4. PHPUnit configuration in `phpunit.xml.dist`
