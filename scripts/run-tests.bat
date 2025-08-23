@echo off
REM Simple Test Runner for Turahe Core
REM This script runs tests with coverage analysis

echo 🧪 Running Turahe Core Tests with Coverage...
echo =============================================

REM Check if PHP is available
php --version >nul 2>&1
if errorlevel 1 (
    echo ❌ PHP is not available. Please install PHP and ensure it's in your PATH.
    pause
    exit /b 1
)

REM Check if PHPUnit is available
if not exist "vendor\bin\phpunit" (
    echo 📦 Installing dependencies...
    composer install --no-interaction
)

REM Create coverage directory
if not exist "coverage" mkdir coverage

REM Set Xdebug mode for coverage
set XDEBUG_MODE=coverage

REM Run tests with coverage
echo 📊 Running tests with coverage analysis...
vendor\bin\phpunit ^
    --coverage-html coverage\html ^
    --coverage-clover coverage\clover.xml ^
    --coverage-text ^
    --coverage-xml coverage\xml ^
    --log-junit coverage\junit.xml ^
    --testdox

REM Check if tests passed
if %ERRORLEVEL% equ 0 (
    echo.
    echo ✅ All tests passed successfully!
    echo.
    echo 📁 Coverage reports generated:
    echo    • HTML Report: coverage\html\index.html
    echo    • Clover XML: coverage\clover.xml
    echo    • Coverage XML: coverage\xml\
    echo    • JUnit XML: coverage\junit.xml
    echo.
    echo 🌐 Open HTML report:
    echo    file:///%CD:\=/%/coverage/html/index.html
) else (
    echo.
    echo ❌ Some tests failed!
    echo Check the output above for details.
)

echo.
echo 🎯 Test run completed!
pause
