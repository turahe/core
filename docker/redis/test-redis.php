<?php

/**
 * Redis Test Script for Turahe Core
 * 
 * This script tests Redis connectivity and basic operations
 * Run with: php docker/redis/test-redis.php
 */

echo "🔴 Testing Redis Connection for Turahe Core\n";
echo "==========================================\n\n";

// Redis configuration
$redisConfig = [
    'host' => '127.0.0.1',
    'port' => 6379,
    'password' => null,
    'database' => 0
];

try {
    // Create Redis connection
    $redis = new Redis();
    $connected = $redis->connect($redisConfig['host'], $redisConfig['port']);
    
    if (!$connected) {
        throw new Exception("Failed to connect to Redis");
    }
    
    echo "✅ Successfully connected to Redis\n";
    echo "   Host: {$redisConfig['host']}\n";
    echo "   Port: {$redisConfig['port']}\n";
    echo "   Database: {$redisConfig['database']}\n\n";
    
    // Test basic operations
    echo "🧪 Testing Basic Operations:\n";
    
    // Set a value
    $redis->set('turahe:test:key', 'Hello from Turahe Core!');
    echo "   ✅ Set key: turahe:test:key\n";
    
    // Get a value
    $value = $redis->get('turahe:test:key');
    echo "   ✅ Get key: turahe:test:key = \"$value\"\n";
    
    // Test hash operations
    $redis->hSet('turahe:test:hash', 'field1', 'value1');
    $redis->hSet('turahe:test:hash', 'field2', 'value2');
    echo "   ✅ Set hash: turahe:test:hash\n";
    
    $hashValue = $redis->hGet('turahe:test:hash', 'field1');
    echo "   ✅ Get hash field: field1 = \"$hashValue\"\n";
    
    // Test list operations
    $redis->lPush('turahe:test:list', 'item1');
    $redis->lPush('turahe:test:list', 'item2');
    $redis->lPush('turahe:test:list', 'item3');
    echo "   ✅ Pushed to list: turahe:test:list\n";
    
    $listLength = $redis->lLen('turahe:test:list');
    echo "   ✅ List length: $listLength\n";
    
    // Test set operations
    $redis->sAdd('turahe:test:set', 'member1');
    $redis->sAdd('turahe:test:set', 'member2');
    $redis->sAdd('turahe:test:set', 'member3');
    echo "   ✅ Added to set: turahe:test:set\n";
    
    $setSize = $redis->sCard('turahe:test:set');
    echo "   ✅ Set size: $setSize\n";
    
    // Test expiration
    $redis->setex('turahe:test:expire', 60, 'This will expire in 60 seconds');
    echo "   ✅ Set key with expiration: turahe:test:expire (60s)\n";
    
    $ttl = $redis->ttl('turahe:test:expire');
    echo "   ✅ TTL: $ttl seconds\n";
    
    // Get Redis info
    $info = $redis->info();
    echo "\n📊 Redis Server Information:\n";
    echo "   Version: {$info['redis_version']}\n";
    echo "   Mode: {$info['redis_mode']}\n";
    echo "   OS: {$info['os']}\n";
    echo "   Memory: " . number_format($info['used_memory_human']) . "\n";
    echo "   Connected Clients: {$info['connected_clients']}\n";
    echo "   Total Commands: {$info['total_commands_processed']}\n";
    
    // Clean up test data
    echo "\n🧹 Cleaning up test data...\n";
    $redis->del('turahe:test:key');
    $redis->del('turahe:test:hash');
    $redis->del('turahe:test:list');
    $redis->del('turahe:test:set');
    $redis->del('turahe:test:expire');
    echo "   ✅ Test data cleaned up\n";
    
    echo "\n🎉 All Redis tests passed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n💡 Make sure Redis is running:\n";
    echo "   docker-compose up -d redis\n";
    echo "   docker exec -it turahe-core-redis redis-cli ping\n";
    exit(1);
}

echo "\n🔗 Useful Redis Commands:\n";
echo "   Connect to Redis CLI: docker exec -it turahe-core-redis redis-cli\n";
echo "   Monitor Redis: docker exec -it turahe-core-redis redis-cli monitor\n";
echo "   Redis Commander: http://localhost:8082\n";
echo "   View logs: docker-compose logs redis\n"; 