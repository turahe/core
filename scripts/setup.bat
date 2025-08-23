@echo off
REM Turahe Core Local Setup Script for Windows

echo 🚀 Setting up Turahe Core local environment...

REM Create .env file if it doesn't exist
if not exist .env (
    echo 📝 Creating .env file for testing...
    (
        echo APP_ENV=testing
        echo APP_KEY=base64:12345678901234567890123456789012=
        echo DB_CONNECTION=sqlite
        echo DB_DATABASE=database/database.sqlite
        echo CACHE_DRIVER=array
        echo SESSION_DRIVER=array
        echo QUEUE_CONNECTION=sync
        echo CORE_TABLE_USE_TIMESTAMPS=false
        echo USERSTAMPS_USERS_TABLE_COLUMN_TYPE=ulid
    ) > .env
    echo ✅ .env file created
) else (
    echo ℹ️  .env file already exists
)

REM Setup SQLite database for testing
echo 🗄️ Setting up SQLite database for testing...
if not exist "database\database.sqlite" (
    echo. > database\database.sqlite
    echo ✅ SQLite database created
) else (
    echo ℹ️ SQLite database already exists
)

REM Setup testing environment
echo 🧪 Setting up testing environment...
echo ✅ Testing environment ready

echo.
echo 🎉 Setup complete!
echo.
echo 📊 Database Information:
echo    Type: SQLite
echo    File: database/database.sqlite
echo    Testing: In-memory SQLite
echo.
echo 🧪 Testing Information:
echo    Framework: PHPUnit
echo    Database: SQLite in-memory
echo    Cache: Array driver
echo.
echo 🔧 Useful Commands:
echo    Run tests: vendor\bin\phpunit
echo    Run with coverage: vendor\bin\phpunit --coverage-html coverage-report
echo    Run specific tests: vendor\bin\phpunit --testsuite=Unit
echo.
echo 📖 For more information, see docs\README.md
pause 