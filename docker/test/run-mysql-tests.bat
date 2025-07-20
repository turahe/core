@echo off
REM MySQL Test Runner for Turahe Core (Windows)
REM This script runs tests using MySQL in Docker environment

echo 🧪 MySQL Test Runner for Turahe Core
echo ====================================
echo.

REM Check if Docker is running
docker info >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker is not running. Please start Docker and try again.
    pause
    exit /b 1
)

REM Check if docker-compose is available
docker-compose --version >nul 2>&1
if errorlevel 1 (
    echo ❌ docker-compose is not installed. Please install docker-compose and try again.
    pause
    exit /b 1
)

echo 📋 Starting MySQL test environment...

REM Start MySQL service
echo 📋 Starting MySQL...
docker-compose up -d mysql

REM Wait for MySQL to be ready
echo 📋 Waiting for MySQL to be ready...
timeout /t 10 /nobreak >nul

REM Check if MySQL is ready
echo 📋 Checking MySQL connection...
:mysql_check
docker exec turahe-core-mysql mysqladmin ping -h"localhost" --silent >nul 2>&1
if errorlevel 1 (
    echo ⚠️  MySQL is not ready yet. Waiting...
    timeout /t 2 /nobreak >nul
    goto mysql_check
)
echo ✅ MySQL is ready!

REM Create test database if it doesn't exist
echo 📋 Setting up test database...
docker exec turahe-core-mysql mysql -u turahe -pturahe123 -e "CREATE DATABASE IF NOT EXISTS turahe_core_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo ✅ Test database ready!

REM Set environment variables for testing
set DB_HOST=127.0.0.1
set DB_PORT=3306
set DB_DATABASE=turahe_core_testing
set DB_USERNAME=turahe
set DB_PASSWORD=turahe123
set DB_CONNECTION=mysql

set REDIS_HOST=127.0.0.1
set REDIS_PORT=6379
set REDIS_DB=1
set REDIS_PASSWORD=null

set CORE_TABLE_USE_TIMESTAMPS=false
set USERSTAMPS_USERS_TABLE_COLUMN_TYPE=ulid
set APP_KEY=base64:MFOsOH9RomiI2LRdgP4hIeoQJ5nyBhdABdH77UY2zi8=

REM Core table names
set CORE_TABLE_SETTINGS=settings
set CORE_TABLE_ORGANIZATIONS=organizations
set CORE_TABLE_MODEL_HAS_ORGANIZATION=model_has_organization
set CORE_TABLE_MODEL_HAS_TAXONOMIES=model_has_taxonomies
set CORE_TABLE_TAXONOMIES=taxonomies
set CORE_TABLE_TAGS=tags
set CORE_TABLE_TAGGABLES=taggables
set CORE_TABLE_OAUTH_ACCOUNTS=oauth_accounts

REM Cache configuration
set CORE_CACHE_ENABLED=true
set CORE_CACHE_SETTINGS_TTL=3600

echo 📋 Environment variables set for MySQL testing

REM Run migrations for testing
echo 📋 Running database migrations...
php artisan migrate --env=testing --force

REM Run tests
echo 📋 Running PHPUnit tests with MySQL...
php vendor/bin/phpunit

REM Check test results
if errorlevel 1 (
    echo ❌ Some tests failed!
    pause
    exit /b 1
) else (
    echo ✅ All tests passed! 🎉
)

echo 📋 Cleaning up...
docker-compose down

echo ✅ MySQL test run completed!
pause 