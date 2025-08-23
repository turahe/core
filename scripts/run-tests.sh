#!/bin/bash

# Simple Test Runner for Turahe Core
# This script runs tests with coverage analysis

echo "🧪 Running Turahe Core Tests with Coverage..."
echo "============================================="

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not available. Please install PHP and ensure it's in your PATH."
    exit 1
fi

# Check if PHPUnit is available
if [ ! -f "vendor/bin/phpunit" ]; then
    echo "📦 Installing dependencies..."
    composer install --no-interaction
fi

# Create coverage directory
mkdir -p coverage

# Set Xdebug mode for coverage
export XDEBUG_MODE=coverage

# Run tests with coverage
echo "📊 Running tests with coverage analysis..."
vendor/bin/phpunit \
    --coverage-html coverage/html \
    --coverage-clover coverage/clover.xml \
    --coverage-text \
    --coverage-xml coverage/xml \
    --log-junit coverage/junit.xml \
    --testdox

# Check if tests passed
if [ $? -eq 0 ]; then
    echo ""
    echo "✅ All tests passed successfully!"
    echo ""
    echo "📁 Coverage reports generated:"
    echo "   • HTML Report: coverage/html/index.html"
    echo "   • Clover XML: coverage/clover.xml"
    echo "   • Coverage XML: coverage/xml/"
    echo "   • JUnit XML: coverage/junit.xml"
    echo ""
    echo "🌐 Open HTML report:"
    echo "   file://$(pwd)/coverage/html/index.html"
else
    echo ""
    echo "❌ Some tests failed!"
    echo "Check the output above for details."
fi

echo ""
echo "🎯 Test run completed!"
