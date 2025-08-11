@echo off
setlocal enabledelayedexpansion

REM Parse command line arguments first
if "%1"=="--help" goto :show_help
if "%1"=="-h" goto :show_help

REM Colors for output (Windows 10+ supports ANSI colors)
set "RED=[91m"
set "GREEN=[92m"
set "YELLOW=[93m"
set "BLUE=[94m"
set "NC=[0m"

REM Function to print colored output
:print_status
echo %BLUE%[INFO]%NC% %~1
goto :eof

:print_success
echo %GREEN%[SUCCESS]%NC% %~1
goto :eof

REM Help function
:show_help
echo Usage: %~nx0 [phpunit-options]
echo.
echo Options:
echo   --help, -h     Show this help message
echo   --filter       Filter tests by name
echo   --testsuite    Run specific test suite
echo   --stop-on-failure  Stop on first failure
echo.
echo Examples:
echo   %~nx0                           # Run all tests
echo   %~nx0 --filter=UserModelTest   # Run specific test
echo   %~nx0 --testsuite=default      # Run default test suite
echo.
exit /b 0

REM Function to check if Docker is running
:check_docker
docker info >nul 2>&1
if errorlevel 1 (
    echo %RED%[ERROR]%NC% Docker is not running. Please start Docker and try again.
    exit /b 1
)
call :print_success "Docker is running"
goto :eof

REM Function to check if Docker Compose is available
:check_docker_compose
docker-compose version >nul 2>&1
if errorlevel 1 (
    docker compose version >nul 2>&1
    if errorlevel 1 (
        echo %RED%[ERROR]%NC% Docker Compose is not available. Please install Docker Compose and try again.
        exit /b 1
    ) else (
        set "COMPOSE_CMD=docker compose"
    )
) else (
    set "COMPOSE_CMD=docker-compose"
)
call :print_success "Docker Compose is available"
goto :eof

REM Function to stop and remove containers
:cleanup
call :print_status "Cleaning up containers..."
%COMPOSE_CMD% down --remove-orphans
call :print_success "Cleanup completed"
goto :eof

REM Function to wait for service to be ready
:wait_for_service
set "service_name=%~1"
set "check_command=%~2"
set "max_attempts=30"
set "attempt=0"

:wait_loop
set /a "attempt+=1"
%check_command% >nul 2>&1
if errorlevel 1 (
    if !attempt! lss !max_attempts! (
        timeout /t 2 /nobreak >nul
        goto :wait_loop
    ) else (
        echo %RED%[ERROR]%NC% Service %service_name% failed to start after !max_attempts! attempts
        exit /b 1
    )
) else (
    call :print_success "%service_name% is ready"
)
goto :eof

REM Main script
:main
call :print_status "Starting test environment setup..."

REM Check prerequisites
call :check_docker
if errorlevel 1 exit /b 1

call :check_docker_compose
if errorlevel 1 exit /b 1

REM Stop any existing containers
call :print_status "Stopping existing containers..."
%COMPOSE_CMD% down --remove-orphans

REM Build and start services
call :print_status "Building and starting services..."
%COMPOSE_CMD% up --build -d mysql redis

REM Wait for services to be ready
call :wait_for_service "MySQL" "docker exec turahe-core-mysql mysqladmin ping -hlocalhost --silent"
if errorlevel 1 exit /b 1

call :wait_for_service "Redis" "docker exec turahe-core-redis redis-cli ping"
if errorlevel 1 exit /b 1

REM Create test database
call :print_status "Creating test database..."
docker exec turahe-core-mysql mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS turahe_core_testing;"
call :print_success "Test database created"

REM Run tests
call :print_status "Running tests..."
%COMPOSE_CMD% run --rm app php vendor/bin/phpunit %*
if errorlevel 1 (
    echo %RED%[ERROR]%NC% Tests failed!
    set "exit_code=1"
) else (
    call :print_success "Tests completed successfully!"
    set "exit_code=0"
)

REM Cleanup
call :cleanup

exit /b !exit_code!

REM Main execution - call main function with all arguments
call :main %*
exit /b %errorlevel%

REM Handle script interruption
:cleanup_on_exit
call :cleanup
exit /b 1
