# Docker Setup for Turahe Core

This directory contains Docker configuration for the Turahe Core Laravel package.

## Services

### MySQL Database
- **Image**: mysql:8.0
- **Port**: 3306
- **Database**: turahe_core
- **Username**: turahe
- **Password**: turahe123
- **Root Password**: root

### phpMyAdmin
- **Image**: phpmyadmin/phpmyadmin:latest
- **Port**: 8081
- **URL**: http://localhost:8081
- **Username**: root
- **Password**: root

### Redis
- **Image**: redis:7-alpine
- **Port**: 6379
- **URL**: redis://localhost:6379
- **Memory Limit**: 256MB
- **Persistence**: AOF enabled

### Redis Commander
- **Image**: rediscommander/redis-commander:latest
- **Port**: 8082
- **URL**: http://localhost:8082
- **Features**: Web-based Redis management interface

### ImgProxy
- **Image**: darthsim/imgproxy:latest
- **Port**: 8080
- **URL**: http://localhost:8080

## Quick Start

1. **Start all services:**
   ```bash
   docker-compose up -d
   ```

2. **Start specific services:**
   ```bash
   # Start only MySQL
   docker-compose up -d mysql
   
   # Start MySQL and phpMyAdmin
   docker-compose up -d mysql phpmyadmin
   
   # Start only Redis
   docker-compose up -d redis
   
   # Start Redis and Redis Commander
   docker-compose up -d redis redis-commander
   ```

3. **Stop services:**
   ```bash
   docker-compose down
   ```

4. **View logs:**
   ```bash
   # All services
   docker-compose logs
   
   # Specific service
   docker-compose logs mysql
   ```

## Database Access

### Using phpMyAdmin
1. Open http://localhost:8081 in your browser
2. Login with:
   - Username: `root`
   - Password: `root`

### Using MySQL Client
```bash
# Connect to MySQL
mysql -h 127.0.0.1 -P 3306 -u turahe -p turahe_core

# Or connect as root
mysql -h 127.0.0.1 -P 3306 -u root -p
```

### Using Docker
```bash
# Connect to MySQL container
docker exec -it turahe-core-mysql mysql -u turahe -p turahe_core

# Or connect as root
docker exec -it turahe-core-mysql mysql -u root -p

# Connect to Redis container
docker exec -it turahe-core-redis redis-cli

# Or connect to Redis with specific database
docker exec -it turahe-core-redis redis-cli -n 1
```

## Environment Configuration

Copy the database configuration to your `.env` file:

```bash
cp docker/database.env .env
```

Or manually add these variables to your `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=turahe_core
DB_USERNAME=turahe
DB_PASSWORD=turahe123

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
```

## Testing Database

For testing, use the `turahe_core_testing` database:

```env
DB_TESTING_CONNECTION=mysql
DB_TESTING_HOST=127.0.0.1
DB_TESTING_PORT=3306
DB_TESTING_DATABASE=turahe_core_testing
DB_TESTING_USERNAME=turahe
DB_TESTING_PASSWORD=turahe123

REDIS_TESTING_HOST=127.0.0.1
REDIS_TESTING_PASSWORD=null
REDIS_TESTING_PORT=6379
REDIS_TESTING_DB=1
```

## Data Persistence

### MySQL
MySQL data is persisted in a Docker volume named `mysql_data`. To reset the database:

```bash
# Stop services
docker-compose down

# Remove volume
docker volume rm turahe-core_mysql_data

# Start services again
docker-compose up -d
```

### Redis
Redis data is persisted in a Docker volume named `redis_data`. To reset Redis:

```bash
# Stop services
docker-compose down

# Remove volume
docker volume rm turahe-core_redis_data

# Start services again
docker-compose up -d
```

## Troubleshooting

### MySQL Connection Issues
1. Ensure the MySQL container is running:
   ```bash
   docker-compose ps
   ```

2. Check MySQL logs:
   ```bash
   docker-compose logs mysql
   ```

3. Wait for MySQL to fully start (may take 30-60 seconds on first run)

### Port Conflicts
If ports 3306, 6379, 8080, 8081, or 8082 are already in use, modify the `docker-compose.yml` file to use different ports.

### Permission Issues
On Linux/macOS, you might need to run:
```bash
sudo chown -R $USER:$USER .
```

## Development Workflow

1. **Start the database:**
   ```bash
   docker-compose up -d mysql
   ```

2. **Run migrations:**
   ```bash
   php artisan migrate
   ```

3. **Seed the database:**
   ```bash
   php artisan db:seed
   ```

4. **Run tests:**
   ```bash
   php artisan test
   ```

5. **Stop when done:**
   ```bash
   docker-compose down
   ``` 