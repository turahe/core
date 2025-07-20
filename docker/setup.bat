@echo off
REM Turahe Core Docker Setup Script for Windows

echo 🚀 Setting up Turahe Core Docker environment...

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

echo ✅ Docker and docker-compose are available

REM Create .env file if it doesn't exist
if not exist .env (
    echo 📝 Creating .env file from database configuration...
    copy docker\database.env .env
    echo ✅ .env file created
) else (
    echo ℹ️  .env file already exists
)

REM Start MySQL service
echo 🐬 Starting MySQL service...
docker-compose up -d mysql

REM Wait for MySQL to be ready
echo ⏳ Waiting for MySQL to be ready...
timeout /t 30 /nobreak >nul

REM Check if MySQL is running
docker-compose ps mysql | findstr "Up" >nul
if errorlevel 1 (
    echo ❌ MySQL failed to start. Check logs with: docker-compose logs mysql
    pause
    exit /b 1
) else (
    echo ✅ MySQL is running
)

REM Start phpMyAdmin if requested
if "%1"=="--with-phpmyadmin" (
    echo 🌐 Starting phpMyAdmin...
    docker-compose up -d phpmyadmin
    echo ✅ phpMyAdmin is available at http://localhost:8081
)

REM Start Redis if requested
if "%1"=="--with-redis" (
    echo 🔴 Starting Redis...
    docker-compose up -d redis
    echo ✅ Redis is available at redis://localhost:6379
)

if "%2"=="--with-redis" (
    echo 🔴 Starting Redis...
    docker-compose up -d redis
    echo ✅ Redis is available at redis://localhost:6379
)

if "%3"=="--with-redis" (
    echo 🔴 Starting Redis...
    docker-compose up -d redis
    echo ✅ Redis is available at redis://localhost:6379
)

REM Start Redis Commander if requested
if "%1"=="--with-redis-commander" (
    echo 🔴 Starting Redis Commander...
    docker-compose up -d redis redis-commander
    echo ✅ Redis Commander is available at http://localhost:8082
)

if "%2"=="--with-redis-commander" (
    echo 🔴 Starting Redis Commander...
    docker-compose up -d redis redis-commander
    echo ✅ Redis Commander is available at http://localhost:8082
)

if "%3"=="--with-redis-commander" (
    echo 🔴 Starting Redis Commander...
    docker-compose up -d redis redis-commander
    echo ✅ Redis Commander is available at http://localhost:8082
)

REM Start ImgProxy if requested
if "%1"=="--with-imgproxy" (
    echo 🖼️  Starting ImgProxy...
    docker-compose up -d imgproxy
    echo ✅ ImgProxy is available at http://localhost:8080
)

if "%2"=="--with-imgproxy" (
    echo 🖼️  Starting ImgProxy...
    docker-compose up -d imgproxy
    echo ✅ ImgProxy is available at http://localhost:8080
)

if "%3"=="--with-imgproxy" (
    echo 🖼️  Starting ImgProxy...
    docker-compose up -d imgproxy
    echo ✅ ImgProxy is available at http://localhost:8080
)

echo.
echo 🎉 Setup complete!
echo.
echo 📊 Database Information:
echo    Host: 127.0.0.1
echo    Port: 3306
echo    Database: turahe_core
echo    Username: turahe
echo    Password: turahe123
echo.
echo 🔴 Redis Information:
echo    Host: 127.0.0.1
echo    Port: 6379
echo    Database: 0
echo    Password: none
echo.
echo 🔧 Useful Commands:
echo    View logs: docker-compose logs
echo    Stop services: docker-compose down
echo    Restart services: docker-compose restart
echo    Access MySQL: docker exec -it turahe-core-mysql mysql -u turahe -p turahe_core
echo    Access Redis: docker exec -it turahe-core-redis redis-cli
echo.
echo 📖 For more information, see docker\README.md
pause 