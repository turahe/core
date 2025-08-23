#!/bin/bash

# Turahe Core Local Setup Script

echo "🚀 Setting up Turahe Core local environment..."

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "📝 Creating .env file for testing..."
    cat > .env << EOF
APP_ENV=testing
APP_KEY=base64:12345678901234567890123456789012=
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
CORE_TABLE_USE_TIMESTAMPS=false
USERSTAMPS_USERS_TABLE_COLUMN_TYPE=ulid
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DB=0
EOF
    echo "✅ .env file created"
else
    echo "ℹ️  .env file already exists"
fi

# Setup SQLite database for testing
echo "🗄️ Setting up SQLite database for testing..."
if [ ! -f "database/database.sqlite" ]; then
    touch database/database.sqlite
    echo "✅ SQLite database created"
else
    echo "ℹ️ SQLite database already exists"
fi

# Setup testing environment
echo "🧪 Setting up testing environment..."
echo "✅ Testing environment ready"

echo ""
echo "🎉 Setup complete!"
echo ""
echo "📊 Database Information:"
echo "   Type: SQLite"
echo "   File: database/database.sqlite"
echo "   Testing: In-memory SQLite"
echo ""
echo "🧪 Testing Information:"
echo "   Framework: PHPUnit"
echo "   Database: SQLite in-memory"
echo "   Cache: Array driver"
echo ""
echo "🔧 Useful Commands:"
echo "   Run tests: vendor/bin/phpunit"
echo "   Run with coverage: vendor/bin/phpunit --coverage-html coverage-report"
echo "   Run specific tests: vendor/bin/phpunit --testsuite=Unit"
echo ""
echo "📖 For more information, see docs/README.md" 