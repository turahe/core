@echo off
REM Turahe Core Test Runner for Windows
REM This script runs tests in the Docker environment

echo 🧪 Turahe Core Test Runner
echo ==========================
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

echo 📋 Starting test environment...

REM Start required services
echo 📋 Starting MySQL and Redis...
docker-compose up -d mysql redis

REM Wait for services to be ready
echo 📋 Waiting for services to be ready...
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

REM Check if Redis is ready
echo 📋 Checking Redis connection...
:redis_check
docker exec turahe-core-redis redis-cli ping >nul 2>&1
if errorlevel 1 (
    echo ⚠️  Redis is not ready yet. Waiting...
    timeout /t 2 /nobreak >nul
    goto redis_check
)
echo ✅ Redis is ready!

REM Build and run the test container
echo 📋 Building test container...
docker-compose build app

echo 📋 Running tests...
docker-compose run --rm app

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

echo ✅ Test run completed!
pause 