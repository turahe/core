#!/bin/bash

# Local Test Runner - No Docker Required
# This script tests the setup without Docker dependencies

echo "🧪 Local Test Runner (No Docker)"
echo "================================="

# Check PHP availability
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not available. Please install PHP and ensure it's in your PATH."
    exit 1
fi

# Check Composer availability
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not available. Please install Composer."
    exit 1
fi

echo "📦 Installing dependencies locally..."
composer install --no-interaction --prefer-dist

if [ $? -ne 0 ]; then
    echo "❌ Composer install failed. Trying alternative approach..."
    composer install --no-interaction --prefer-source
    if [ $? -ne 0 ]; then
        echo "❌ Could not install dependencies."
        exit 1
    fi
fi

echo "✅ Dependencies installed successfully!"

echo "🧪 Running tests..."
vendor/bin/phpunit --testdox

if [ $? -eq 0 ]; then
    echo "✅ All tests passed!"
else
    echo "❌ Some tests failed. Check output above."
fi

echo "🎯 Local test completed!"
