<?php

/**
 * Core package configuration file.
 *
 * This file contains configuration options for database tables and Imgproxy integration.
 *
 * @return array
 */
return [
    /**
     * --------------------------------------------------------------------------
     * Database Table Names
     * --------------------------------------------------------------------------
     *
     * Here you may specify the table names used by the core package. You can
     * override these by setting the corresponding environment variables.
     */
    'tables' => [
        // Table for storing settings
        'settings' => env('CORE_TABLE_SETTINGS', 'settings'),

        // Table for organizations
        'organizations' => env('CORE_TABLE_ORGANIZATIONS', 'organizations'),

        // Pivot table for model-organization relationships
        'model_has_organization' => env('CORE_TABLE_MODEL_HAS_ORGANIZATION', 'model_has_organization'),

        // Table for taxonomies (e.g., categories, types)
        'taxonomies' => env('CORE_TABLE_TAXONOMIES', 'taxonomies'),

        // Pivot table for model-taxonomy relationships
        'model_has_taxonomies' => env('CORE_TABLE_MODEL_HAS_TAXONOMIES', 'model_has_taxonomies'),

        // Table for tags
        'tags' => env('CORE_TABLE_TAGS', 'tags'),

        // Pivot table for taggables (polymorphic tag relationships)
        'taggables' => env('CORE_TABLE_TAGGABLES', 'taggables'),

        // Table for OAuth account connections
        'oauth_accounts' => env('CORE_TABLE_OAUTH_ACCOUNTS', 'oauth_accounts'),
    ],

    /**
     * --------------------------------------------------------------------------
     * Cache Configuration
     * --------------------------------------------------------------------------
     *
     * These options configure caching behavior for the core package.
     * You can override these by setting the corresponding environment variables.
     */
    'cache' => [
        // Time-to-live for settings cache in seconds (default: 1 hour)
        'settings_ttl' => env('CORE_CACHE_SETTINGS_TTL', 3600),

        // Enable/disable caching (default: true)
        'enabled' => env('CORE_CACHE_ENABLED', true),
    ],

    /**
     * --------------------------------------------------------------------------
     * Imgproxy Configuration
     * --------------------------------------------------------------------------
     *
     * These options configure the Imgproxy image processing service integration.
     * You can override these by setting the corresponding environment variables.
     */
    'imgproxy' => [
        // The size of the signature key in bytes
        'signature_size' => env('IMGPROXY_SIGNATURE_SIZE', 32),

        // The Imgproxy key for signing URLs (hex-encoded)
        'key' => env('IMGPROXY_KEY', '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef'),

        // The Imgproxy salt for signing URLs (hex-encoded)
        'salt' => env('IMGPROXY_SALT', '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef'),

        // Allowed resize types for images
        'resize_values' => env('IMGPROXY_RESIZE_VALUES', ['fit', 'fill', 'crop', 'stretch', 'pad']),

        // Maximum allowed dimension (in pixels) for width or height
        'max_dim_px' => env('IMGPROXY_MAX_DIM_PX', 10000),

        // Allowed gravity values for cropping and positioning
        'gravity_values' => env('IMGPROXY_GRAVITY_VALUES', [
            'no', 'sm', 'md', 'lg', 'xl', 'xxl', 'center', 'top', 'bottom', 'left', 'right',
        ]),

        // Default file extension for processed images
        'default_extension' => env('IMGPROXY_DEFAULT_EXTENSION', 'jpg'),

        // Supported image formats for output
        'formats' => env('IMGPROXY_FORMATS', ['jpg', 'jpeg', 'png', 'webp']),
    ],
];
