-- Create database if not exists
CREATE DATABASE IF NOT EXISTS turahe_core CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE turahe_core;

-- Create additional databases for testing if needed
CREATE DATABASE IF NOT EXISTS turahe_core_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant privileges to the turahe user
GRANT ALL PRIVILEGES ON turahe_core.* TO 'turahe'@'%';
GRANT ALL PRIVILEGES ON turahe_core_testing.* TO 'turahe'@'%';

-- Flush privileges
FLUSH PRIVILEGES; 