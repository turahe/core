@echo off
REM Local Test Runner - No Docker Required
REM This script tests the setup without Docker dependencies

echo 🧪 Local Test Runner (No Docker)
echo =================================

REM Check PHP availability
php --version >nul 2>&1
if errorlevel 1 (
    echo ❌ PHP is not available. Please install PHP and ensure it's in your PATH.
    pause
    exit /b 1
)

REM Check Composer availability
composer --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Composer is not available. Please install Composer.
    pause
    exit /b 1
)

echo 📦 Installing dependencies locally...
composer install --no-interaction --prefer-dist

if errorlevel 1 (
    echo ❌ Composer install failed. Trying alternative approach...
    composer install --no-interaction --prefer-source
    if errorlevel 1 (
        echo ❌ Could not install dependencies.
        pause
        exit /b 1
    )
)

echo ✅ Dependencies installed successfully!

echo 🧪 Running tests...
vendor\bin\phpunit --testdox

if %ERRORLEVEL% equ 0 (
    echo ✅ All tests passed!
) else (
    echo ❌ Some tests failed. Check output above.
)

echo 🎯 Local test completed!
pause
