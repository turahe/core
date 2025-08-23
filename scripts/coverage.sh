#!/bin/bash

# PHPUnit Coverage Runner
# This script runs PHPUnit with comprehensive coverage reporting

echo "🧪 Running PHPUnit with Coverage Analysis..."
echo "============================================="

# Ensure coverage directory exists
mkdir -p coverage

# Run PHPUnit with coverage
echo "📊 Generating coverage reports..."
vendor/bin/phpunit \
    --coverage-html coverage/html \
    --coverage-clover coverage/clover.xml \
    --coverage-text \
    --coverage-xml coverage/xml \
    --log-junit coverage/junit.xml

# Check if coverage was successful
if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Coverage analysis completed successfully!"
    echo ""
    echo "📁 Reports generated:"
    echo "   • HTML Report: coverage/html/index.html"
    echo "   • Clover XML: coverage/clover.xml"
    echo "   • Coverage XML: coverage/xml/"
    echo "   • JUnit XML: coverage/junit.xml"
    echo ""
    echo "🌐 Open HTML report:"
    echo "   file://$(pwd)/coverage/html/index.html"
else
    echo ""
    echo "❌ Coverage analysis failed!"
    exit 1
fi
