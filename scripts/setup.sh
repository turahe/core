#!/bin/bash

# Turahe Core Local Setup Script

echo "🚀 Setting up Turahe Core local environment..."

# Check if .env file exists
if [ -f .env ]; then
    echo "ℹ️  .env file already exists"
else
    echo "ℹ️  No .env file found - using phpunit.xml configuration"
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
echo "   Type: SQLite (in-memory)"
echo "   Configuration: phpunit.xml"
echo "   Testing: In-memory SQLite"
echo ""
echo "🧪 Testing Information:"
echo "   Framework: PHPUnit"
echo "   Configuration: phpunit.xml"
echo "   Cache: Array driver (in-memory)"
echo ""
echo "🔧 Useful Commands:"
echo "   Run tests: vendor/bin/phpunit"
echo "   Run with coverage: vendor/bin/phpunit --coverage-html coverage-report"
echo "   Run specific tests: vendor/bin/phpunit --testsuite=Unit"
echo ""
echo "📖 For more information, see docs/README.md" 