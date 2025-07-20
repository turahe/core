<?php

/**
 * Simple Test Script for Turahe Core
 * Tests basic functionality without requiring full Laravel setup
 */

echo "🧪 Simple Test for Turahe Core\n";
echo "==============================\n\n";

// Test 1: PHP Version
echo "1. Testing PHP Version...\n";
$phpVersion = PHP_VERSION;
echo "   PHP Version: $phpVersion\n";
if (version_compare($phpVersion, '8.0.0', '>=')) {
    echo "   ✅ PHP version is compatible\n";
} else {
    echo "   ❌ PHP version is too old (requires 8.0+)\n";
}

echo "\n";

// Test 2: Required Extensions
echo "2. Testing Required Extensions...\n";
$required = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
$missing = [];

foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✅ $ext extension loaded\n";
    } else {
        echo "   ❌ $ext extension missing\n";
        $missing[] = $ext;
    }
}

if (empty($missing)) {
    echo "   ✅ All required extensions are loaded\n";
} else {
    echo "   ❌ Missing extensions: " . implode(', ', $missing) . "\n";
}

echo "\n";

// Test 3: Optional Extensions
echo "3. Testing Optional Extensions...\n";
$optional = ['redis', 'gd', 'zip'];
foreach ($optional as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✅ $ext extension loaded\n";
    } else {
        echo "   ⚠️  $ext extension not loaded (optional)\n";
    }
}

echo "\n";

// Test 4: File System
echo "4. Testing File System...\n";
$paths = [
    'composer.json' => 'Composer configuration',
    'src/' => 'Source code directory',
    'tests/' => 'Tests directory',
    'database/migrations/' => 'Database migrations'
];

foreach ($paths as $path => $description) {
    if (file_exists($path)) {
        echo "   ✅ $description found\n";
    } else {
        echo "   ❌ $description missing\n";
    }
}

echo "\n";

// Test 5: Composer Autoloader
echo "5. Testing Composer Autoloader...\n";
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    echo "   ✅ Composer autoloader loaded\n";
    
    // Test if we can load some basic classes
    if (class_exists('Turahe\Core\CoreServiceProvider')) {
        echo "   ✅ Turahe Core classes can be loaded\n";
    } else {
        echo "   ⚠️  Turahe Core classes not found\n";
    }
} else {
    echo "   ❌ Composer autoloader not found\n";
    echo "   💡 Run: composer install\n";
}

echo "\n";

// Test 6: Environment Variables
echo "6. Testing Environment Variables...\n";
$envVars = [
    'DB_HOST' => 'Database Host',
    'DB_DATABASE' => 'Database Name',
    'REDIS_HOST' => 'Redis Host'
];

foreach ($envVars as $var => $description) {
    $value = $_ENV[$var] ?? getenv($var);
    if ($value) {
        echo "   ✅ $description: $value\n";
    } else {
        echo "   ⚠️  $description not set\n";
    }
}

echo "\n";

// Test 7: Basic Database Connection (if possible)
echo "7. Testing Database Connection...\n";
$dbHost = $_ENV['DB_HOST'] ?? 'mysql';
$dbName = $_ENV['DB_DATABASE'] ?? 'turahe_core_testing';
$dbUser = $_ENV['DB_USERNAME'] ?? 'turahe';
$dbPass = $_ENV['DB_PASSWORD'] ?? 'turahe123';

try {
    $dsn = "mysql:host=$dbHost;dbname=$dbName";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✅ Database connection successful\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   📊 Tables in database: {$result['count']}\n";
    
} catch (PDOException $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "   💡 Make sure MySQL service is running\n";
}

echo "\n";

// Test 8: Basic Redis Connection (if possible)
echo "8. Testing Redis Connection...\n";
if (extension_loaded('redis')) {
    $redisHost = $_ENV['REDIS_HOST'] ?? 'redis';
    $redisPort = $_ENV['REDIS_PORT'] ?? 6379;
    
    try {
        $redis = new Redis();
        $connected = $redis->connect($redisHost, $redisPort);
        
        if ($connected) {
            echo "   ✅ Redis connection successful\n";
            
            // Test basic operations
            $redis->set('turahe:test:simple', 'success');
            $value = $redis->get('turahe:test:simple');
            echo "   📊 Test operation: $value\n";
            
            // Clean up
            $redis->del('turahe:test:simple');
            
        } else {
            echo "   ❌ Redis connection failed\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Redis connection failed: " . $e->getMessage() . "\n";
        echo "   💡 Make sure Redis service is running\n";
    }
} else {
    echo "   ⚠️  Redis extension not loaded\n";
}

echo "\n";

echo "🎉 Simple test completed!\n";
echo "💡 For full testing, run: php vendor/bin/phpunit\n"; 