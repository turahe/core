<?php

/**
 * Quick Test Script for Turahe Core
 * Tests basic functionality without requiring full Docker build
 */

echo "⚡ Quick Test for Turahe Core\n";
echo "============================\n\n";

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

// Test 2: Basic Extensions
echo "2. Testing Basic Extensions...\n";
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

// Test 3: File System
echo "3. Testing File System...\n";
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

// Test 4: Composer Autoloader
echo "4. Testing Composer Autoloader...\n";
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    echo "   ✅ Composer autoloader loaded\n";
    
    // Test if we can load some classes
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

// Test 5: Docker Services (if running)
echo "5. Testing Docker Services...\n";

// Test MySQL connection
$mysqlHost = '127.0.0.1';
$mysqlPort = 3306;
$mysqlUser = 'turahe';
$mysqlPass = 'turahe123';
$mysqlDb = 'turahe_core_testing';

try {
    $dsn = "mysql:host=$mysqlHost;port=$mysqlPort;dbname=$mysqlDb";
    $pdo = new PDO($dsn, $mysqlUser, $mysqlPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✅ MySQL connection successful\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   📊 MySQL Version: {$version['version']}\n";
    
} catch (PDOException $e) {
    echo "   ❌ MySQL connection failed: " . $e->getMessage() . "\n";
    echo "   💡 Make sure MySQL service is running: docker-compose up -d mysql\n";
}

// Test Redis connection
$redisHost = '127.0.0.1';
$redisPort = 6379;

if (extension_loaded('redis')) {
    try {
        $redis = new Redis();
        $connected = $redis->connect($redisHost, $redisPort);
        
        if ($connected) {
            echo "   ✅ Redis connection successful\n";
            
            // Test basic operations
            $redis->set('turahe:test:quick', 'success');
            $value = $redis->get('turahe:test:quick');
            echo "   📊 Test operation: $value\n";
            
            // Clean up
            $redis->del('turahe:test:quick');
            
        } else {
            echo "   ❌ Redis connection failed\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Redis connection failed: " . $e->getMessage() . "\n";
        echo "   💡 Make sure Redis service is running: docker-compose up -d redis\n";
    }
} else {
    echo "   ⚠️  Redis extension not loaded\n";
}

echo "\n";

// Test 6: Basic Organization Model Test
echo "6. Testing Organization Model...\n";
if (file_exists('vendor/autoload.php')) {
    try {
        // Test if we can create an organization instance
        if (class_exists('Turahe\Core\Models\Organization')) {
            echo "   ✅ Organization model class found\n";
            
            // Test enum
            if (class_exists('Turahe\Core\Enums\OrganizationType')) {
                echo "   ✅ OrganizationType enum found\n";
                
                // Test enum values
                $types = \Turahe\Core\Enums\OrganizationType::cases();
                echo "   📊 Available organization types: " . count($types) . "\n";
                
            } else {
                echo "   ❌ OrganizationType enum not found\n";
            }
            
        } else {
            echo "   ❌ Organization model class not found\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Error testing Organization model: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠️  Cannot test models without autoloader\n";
}

echo "\n";

echo "🎉 Quick test completed!\n";
echo "💡 For full testing, run: php vendor/bin/phpunit\n";
echo "💡 For Docker testing, run: docker-compose run --rm app php vendor/bin/phpunit\n"; 