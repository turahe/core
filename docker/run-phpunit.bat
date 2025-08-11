@echo off
setlocal enabledelayedexpansion

echo [INFO] Running PHPUnit tests with Docker...

REM Check if Docker is running
docker info >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker is not running. Please start Docker and try again.
    exit /b 1
)

REM Build the test image
echo [INFO] Building test image...
docker build -f docker/test/Dockerfile -t turahe-core-test .

if errorlevel 1 (
    echo [ERROR] Failed to build test image
    exit /b 1
)

REM Run tests
echo [INFO] Running tests...
docker run --rm turahe-core-test php vendor/bin/phpunit %*

if errorlevel 1 (
    echo [ERROR] Tests failed!
    exit /b 1
) else (
    echo [SUCCESS] Tests completed successfully!
)

REM Clean up image
echo [INFO] Cleaning up test image...
docker rmi turahe-core-test >nul 2>&1

REM Stop any running containers
echo [INFO] Stopping any running containers...
if exist docker-compose.yml (
    docker-compose down >nul 2>&1
    if errorlevel 1 (
        docker compose down >nul 2>&1
    )
    echo [SUCCESS] Containers stopped
)
