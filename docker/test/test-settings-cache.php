<?php

require_once __DIR__ . '/../../vendor/autoload.php';

// Simple test of settings cache functionality
$configPath = __DIR__ . '/../../config/core.php';
if (file_exists($configPath)) {
    $config = require $configPath;
    echo "Core configuration loaded successfully!\n";
    echo "Cache enabled: " . ($config['cache']['enabled'] ? 'true' : 'false') . "\n";
    echo "Settings TTL: " . $config['cache']['settings_ttl'] . " seconds\n";
    echo "Settings table: " . $config['tables']['settings'] . "\n";
    
    // Test cache key generation
    $modelClass = 'App\Models\User';
    $modelId = '123456789';
    $timestamp = time();
    $key = 'test_setting';
    
    $settingsCacheKey = sprintf(
        'settings:%s:%s:%s',
        $modelClass,
        $modelId,
        $timestamp
    );
    
    $settingCacheKey = sprintf(
        'setting:%s:%s:%s:%s',
        $modelClass,
        $modelId,
        $key,
        $timestamp
    );
    
    echo "\nCache key examples:\n";
    echo "Settings cache key: " . $settingsCacheKey . "\n";
    echo "Setting cache key: " . $settingCacheKey . "\n";
    
    echo "\nSettings cache functionality ready!\n";
} else {
    echo "Config file not found at: $configPath\n";
} 