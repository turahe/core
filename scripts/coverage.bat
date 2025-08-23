@echo off
REM PHPUnit Coverage Runner for Windows
REM This script runs PHPUnit with comprehensive coverage reporting

echo 🧪 Running PHPUnit with Coverage Analysis...
echo =============================================

REM Ensure coverage directory exists
if not exist coverage mkdir coverage

REM Run PHPUnit with coverage
echo 📊 Generating coverage reports...
vendor\bin\phpunit ^
    --coverage-html coverage\html ^
    --coverage-clover coverage\clover.xml ^
    --coverage-text ^
    --coverage-xml coverage\xml ^
    --log-junit coverage\junit.xml

REM Check if coverage was successful
if %ERRORLEVEL% equ 0 (
    echo.
    echo ✅ Coverage analysis completed successfully!
    echo.
    echo 📁 Reports generated:
    echo    • HTML Report: coverage\html\index.html
    echo    • Clover XML: coverage\clover.xml
    echo    • Coverage XML: coverage\xml\
    echo    • JUnit XML: coverage\junit.xml
    echo.
    echo 🌐 Open HTML report:
    echo    file:///%CD:\=/%/coverage/html/index.html
) else (
    echo.
    echo ❌ Coverage analysis failed!
    exit /b 1
)
