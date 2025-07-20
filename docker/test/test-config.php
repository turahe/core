<?php

require_once __DIR__ . '/../../vendor/autoload.php';

// Simple test of config loading
$configPath = __DIR__ . '/../../config/core.php';
if (file_exists($configPath)) {
    $config = require $configPath;
    echo "Core configuration loaded successfully!\n";
    echo "Settings table: " . $config['tables']['settings'] . "\n";
    echo "Organizations table: " . $config['tables']['organizations'] . "\n";
    echo "Taxonomies table: " . $config['tables']['taxonomies'] . "\n";
    echo "Tags table: " . $config['tables']['tags'] . "\n";
    echo "OAuth accounts table: " . $config['tables']['oauth_accounts'] . "\n";
    echo "Use timestamps: " . ($config['table']['use_timestamps'] ? 'true' : 'false') . "\n";
} else {
    echo "Config file not found at: $configPath\n";
} 