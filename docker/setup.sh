#!/bin/bash

# Turahe Core Docker Setup Script

echo "🚀 Setting up Turahe Core Docker environment..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker and try again."
    exit 1
fi

# Check if docker-compose is available
if ! command -v docker-compose &> /dev/null; then
    echo "❌ docker-compose is not installed. Please install docker-compose and try again."
    exit 1
fi

echo "✅ Docker and docker-compose are available"

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "📝 Creating .env file from database configuration..."
    cp docker/database.env .env
    echo "✅ .env file created"
else
    echo "ℹ️  .env file already exists"
fi

# Start MySQL service
echo "🐬 Starting MySQL service..."
docker-compose up -d mysql

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 30

# Check if MySQL is running
if docker-compose ps mysql | grep -q "Up"; then
    echo "✅ MySQL is running"
else
    echo "❌ MySQL failed to start. Check logs with: docker-compose logs mysql"
    exit 1
fi

# Start phpMyAdmin if requested
if [ "$1" = "--with-phpmyadmin" ]; then
    echo "🌐 Starting phpMyAdmin..."
    docker-compose up -d phpmyadmin
    echo "✅ phpMyAdmin is available at http://localhost:8081"
fi

# Start Redis if requested
if [ "$1" = "--with-redis" ] || [ "$2" = "--with-redis" ] || [ "$3" = "--with-redis" ]; then
    echo "🔴 Starting Redis..."
    docker-compose up -d redis
    echo "✅ Redis is available at redis://localhost:6379"
fi

# Start Redis Commander if requested
if [ "$1" = "--with-redis-commander" ] || [ "$2" = "--with-redis-commander" ] || [ "$3" = "--with-redis-commander" ]; then
    echo "🔴 Starting Redis Commander..."
    docker-compose up -d redis redis-commander
    echo "✅ Redis Commander is available at http://localhost:8082"
fi

# Start ImgProxy if requested
if [ "$1" = "--with-imgproxy" ] || [ "$2" = "--with-imgproxy" ] || [ "$3" = "--with-imgproxy" ]; then
    echo "🖼️  Starting ImgProxy..."
    docker-compose up -d imgproxy
    echo "✅ ImgProxy is available at http://localhost:8080"
fi

echo ""
echo "🎉 Setup complete!"
echo ""
echo "📊 Database Information:"
echo "   Host: 127.0.0.1"
echo "   Port: 3306"
echo "   Database: turahe_core"
echo "   Username: turahe"
echo "   Password: turahe123"
echo ""
echo "🔴 Redis Information:"
echo "   Host: 127.0.0.1"
echo "   Port: 6379"
echo "   Database: 0"
echo "   Password: none"
echo ""
echo "🔧 Useful Commands:"
echo "   View logs: docker-compose logs"
echo "   Stop services: docker-compose down"
echo "   Restart services: docker-compose restart"
echo "   Access MySQL: docker exec -it turahe-core-mysql mysql -u turahe -p turahe_core"
echo "   Access Redis: docker exec -it turahe-core-redis redis-cli"
echo ""
echo "📖 For more information, see docker/README.md" 