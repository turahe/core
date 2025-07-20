<?php

/**
 * Connection Test Script for Turahe Core
 * Tests MySQL and Redis connections in Docker environment
 */

echo "🔍 Testing Connections for Turahe Core\n";
echo "=====================================\n\n";

// Test configuration
$config = [
    'mysql' => [
        'host' => $_ENV['DB_HOST'] ?? 'mysql',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'database' => $_ENV['DB_DATABASE'] ?? 'turahe_core_testing',
        'username' => $_ENV['DB_USERNAME'] ?? 'turahe',
        'password' => $_ENV['DB_PASSWORD'] ?? 'turahe123'
    ],
    'redis' => [
        'host' => $_ENV['REDIS_HOST'] ?? 'redis',
        'port' => $_ENV['REDIS_PORT'] ?? 6379,
        'database' => $_ENV['REDIS_DB'] ?? 1
    ]
];

$errors = [];

// Test MySQL Connection
echo "🐬 Testing MySQL Connection...\n";
try {
    $dsn = "mysql:host={$config['mysql']['host']};port={$config['mysql']['port']};dbname={$config['mysql']['database']}";
    $pdo = new PDO($dsn, $config['mysql']['username'], $config['mysql']['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "   ✅ MySQL connected successfully\n";
    echo "   📊 Host: {$config['mysql']['host']}:{$config['mysql']['port']}\n";
    echo "   📊 Database: {$config['mysql']['database']}\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   📊 MySQL Version: {$version['version']}\n";
    
} catch (PDOException $e) {
    echo "   ❌ MySQL connection failed: " . $e->getMessage() . "\n";
    $errors[] = "MySQL: " . $e->getMessage();
}

echo "\n";

// Test Redis Connection
echo "🔴 Testing Redis Connection...\n";
try {
    if (extension_loaded('redis')) {
        $redis = new Redis();
        $connected = $redis->connect($config['redis']['host'], $config['redis']['port']);
        
        if ($connected) {
            $redis->select($config['redis']['database']);
            echo "   ✅ Redis connected successfully\n";
            echo "   📊 Host: {$config['redis']['host']}:{$config['redis']['port']}\n";
            echo "   📊 Database: {$config['redis']['database']}\n";
            
            // Test basic operations
            $redis->set('turahe:test:connection', 'success');
            $value = $redis->get('turahe:test:connection');
            echo "   📊 Test operation: $value\n";
            
            // Get Redis info
            $info = $redis->info();
            echo "   📊 Redis Version: {$info['redis_version']}\n";
            
            // Clean up
            $redis->del('turahe:test:connection');
            
        } else {
            throw new Exception("Failed to connect to Redis");
        }
    } else {
        throw new Exception("Redis extension not loaded");
    }
    
} catch (Exception $e) {
    echo "   ❌ Redis connection failed: " . $e->getMessage() . "\n";
    $errors[] = "Redis: " . $e->getMessage();
}

echo "\n";

// Test PHP Extensions
echo "🔧 Testing PHP Extensions...\n";
$required_extensions = ['pdo', 'pdo_mysql', 'redis', 'mbstring', 'json'];
$loaded_extensions = get_loaded_extensions();

foreach ($required_extensions as $ext) {
    if (in_array($ext, $loaded_extensions)) {
        echo "   ✅ $ext extension loaded\n";
    } else {
        echo "   ❌ $ext extension not loaded\n";
        $errors[] = "PHP Extension: $ext not loaded";
    }
}

echo "\n";

// Test Composer Autoloader
echo "📦 Testing Composer Autoloader...\n";
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    echo "   ✅ Composer autoloader loaded\n";
    
    // Test if we can load some classes
    try {
        if (class_exists('Turahe\Core\CoreServiceProvider')) {
            echo "   ✅ Turahe Core classes can be loaded\n";
        } else {
            echo "   ⚠️  Turahe Core classes not found\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Error loading Turahe Core classes: " . $e->getMessage() . "\n";
        $errors[] = "Autoloader: " . $e->getMessage();
    }
} else {
    echo "   ❌ Composer autoloader not found\n";
    $errors[] = "Composer autoloader not found";
}

echo "\n";

// Summary
if (empty($errors)) {
    echo "🎉 All connection tests passed!\n";
    echo "✅ Environment is ready for testing\n";
    exit(0);
} else {
    echo "❌ Some tests failed:\n";
    foreach ($errors as $error) {
        echo "   - $error\n";
    }
    echo "\n💡 Please check your Docker services and configuration.\n";
    exit(1);
} 