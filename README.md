[![PHP Composer](https://github.com/turahe/core/actions/workflows/php.yml/badge.svg)](https://github.com/turahe/core/actions/workflows/php.yml)
[![Tests](https://img.shields.io/badge/tests-196%20passing-brightgreen)](https://github.com/turahe/core)
[![PHP Version](https://img.shields.io/badge/php-8.2%2B-blue.svg)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-8%2B%20%7C%209%2B%20%7C%2010%2B-orange.svg)](https://laravel.com)

# Turahe Core SDK

A comprehensive Laravel package providing essential core functionality including image processing, OAuth integration, taxonomy management, and utility helpers.

## 🚀 Features

### 📸 Image Processing
- **ImgProxy Integration**: Advanced image resizing, cropping, and format conversion
- **Image Signatures**: Secure URL generation with HMAC signatures
- **Preset Support**: Predefined image transformation presets
- **Multiple Formats**: Support for JPG, PNG, WebP, and more

### 🔐 OAuth Integration
- **Google OAuth**: Complete Google API integration with Calendar and Gmail support
- **Microsoft Graph**: Microsoft 365 integration with batch operations
- **Token Management**: Secure access token encryption and refresh handling
- **Resource Owners**: Custom resource owner implementations

### 🏷️ Taxonomy & Tagging System
- **Hierarchical Taxonomies**: Nested category management with parent-child relationships
- **Tag System**: Flexible tagging with type support and scopes
- **Slug Generation**: Automatic slug creation with configurable sluggers
- **Sortable**: Built-in sorting capabilities

### 🏢 Organization Management
- **Multi-tenant Support**: Organization-based data isolation
- **Hierarchical Structure**: Parent-child organization relationships
- **Settings Management**: Per-organization configuration storage

### 📧 Email Processing
- **Mail Headers**: Advanced email header parsing and manipulation
- **Address Headers**: RFC-compliant email address handling
- **Date Headers**: Carbon-based date parsing
- **Embedded Images**: Automatic image processing in emails

### 🛠️ Utility Helpers
- **Currency Formatting**: Multi-currency support with locale-specific formatting
- **Name Aliases**: Generate initials from full names
- **String Cleaning**: URL-safe string sanitization
- **Acronym Generation**: Extract acronyms from text
- **Phone Parsing**: International phone number validation and formatting
- **Percentage Calculations**: Mathematical percentage operations

## 📦 Installation

1. Install the package via composer:

    ```shell
    composer require turahe/core
    ```

2. Publish resources (migrations and config files):

    ```shell
    php artisan vendor:publish --provider="Turahe\Core\CoreServiceProvider"
    ```

3. Execute migrations via the following command:

    ```shell
    php artisan migrate
    ```

4. Configure your environment variables:

    ```env
    # Image Processing
    IMGPROXY_BASE_URL=https://your-imgproxy-server.com
    IMGPROXY_KEY=your-32-byte-hex-key
    IMGPROXY_SALT=your-32-byte-hex-salt
    IMGPROXY_MAX_DIM_PX=10000
    IMGPROXY_SIGNATURE_SIZE=32

    # OAuth Configuration
    GOOGLE_CLIENT_ID=your-google-client-id
    GOOGLE_CLIENT_SECRET=your-google-client-secret
    MICROSOFT_CLIENT_ID=your-microsoft-client-id
    MICROSOFT_CLIENT_SECRET=your-microsoft-client-secret
    ```

5. Done!

## 🔧 Configuration

### Image Processing Configuration

The package uses the `config/core.php` file for image processing settings:

```php
'imgproxy' => [
    'signature_size' => env('IMGPROXY_SIGNATURE_SIZE', 32),
    'key' => env('IMGPROXY_KEY', '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef'),
    'salt' => env('IMGPROXY_SALT', '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef'),
    'resize_values' => ['fit', 'fill', 'crop', 'stretch', 'pad'],
    'max_dim_px' => env('IMGPROXY_MAX_DIM_PX', 10000),
    'gravity_values' => ['no', 'sm', 'md', 'lg', 'xl', 'xxl', 'center', 'top', 'bottom', 'left', 'right'],
    'default_extension' => env('IMGPROXY_DEFAULT_EXTENSION', 'jpg'),
    'formats' => ['jpg', 'jpeg', 'png', 'webp'],
],
```

## 📖 Usage Examples

### Image Processing

```php
use Turahe\Core\Services\Image\Image;

// Create an image with custom dimensions
$image = new Image();
$image->make('/path/to/image.jpg', 800, 600, 'webp');

// Use presets
$image->makePreset('/path/to/image.jpg', 'thumbnail', 'png');

// Generate signed URL
$signature = new ImageSignature($image);
$signedUrl = $signature->take();
```

### Helper Functions

```php
// Currency formatting
echo format_currency(1000000); // "Rp 1.000.000"

// Name aliases
echo name_alias('John Doe'); // "JD"

// String cleaning
echo clean('Hello World!'); // "Hello-World"

// Acronyms
echo acronym('PHP Hypertext Preprocessor'); // "PHP"

// Phone parsing
echo parse_phone('08123456789'); // "+628123456789"

// Percentage calculations
echo calculate_percentage(100, 25); // 25
```

### Taxonomy Management

```php
use Turahe\Core\Models\Taxonomy;

// Create taxonomies
$category = Taxonomy::createTaxonomy('Technology');
$subcategory = Taxonomy::createTaxonomy('Programming', 'PROG', $category);

// Create multiple taxonomies
$taxonomies = Taxonomy::createTaxonomies(['PHP', 'Laravel', 'Vue.js']);
```

### Tag System

```php
use Turahe\Core\Models\Tag;

// Create tags
$tag = Tag::createTag('important', 'priority');

// Find or create tags
$tag = Tag::findOrCreateTag('urgent', 'priority');

// Get tags by type
$priorityTags = Tag::getAllByType('priority');
```

### OAuth Integration

```php
use Turahe\Core\OAuth\OAuthManager;

// Google OAuth
$googleManager = new OAuthManager('google');
$authUrl = $googleManager->getAuthorizationUrl();

// Microsoft Graph
$msManager = new OAuthManager('microsoft');
$accessToken = $msManager->getAccessToken();
```

## 🧪 Testing

The package includes comprehensive test coverage with **196 tests** and **418 assertions**.

### Running Tests

```shell
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit tests/Unit/ImageTest.php
./vendor/bin/phpunit tests/Unit/HelpersTest.php

# Run with testdox format
./vendor/bin/phpunit --testdox
```

### Test Coverage

- **Image Processing**: 43 tests covering all Image class methods
- **Helper Functions**: 33 tests for utility functions
- **Image Signatures**: 8 tests for secure URL generation
- **OAuth Integration**: 3 tests for token management
- **Taxonomy System**: 5 tests for category management
- **Tag System**: 16 tests for tagging functionality
- **Organization Management**: 8 tests for multi-tenant support
- **Email Processing**: 11 tests for mail header handling
- **Model Concerns**: 57 tests for trait functionality

## 🔌 Service Providers

The package automatically registers the following service providers:

- `Turahe\Core\CoreServiceProvider` - Main service provider
- `Spatie\EloquentSortable\EloquentSortableServiceProvider` - Sorting functionality
- `Turahe\UserStamps\UserStampsServiceProvider` - User tracking

## 📋 Requirements

- **PHP**: 8.2 or higher
- **Laravel**: 8.x, 9.x, or 10.x
- **Extensions**: intl, iconv

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Setup

```shell
# Clone the repository
git clone https://github.com/turahe/core.git

# Install dependencies
composer install

# Run tests
./vendor/bin/phpunit

# Run code style checks
./vendor/bin/pint
```

## 📄 License

This package is proprietary software. All rights reserved.

## 👨‍💻 Author

**Nur Wachid** - [wachid@outlook.com](mailto:wachid@outlook.com)

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP framework
- [Spatie](https://spatie.be) - Eloquent Sortable package
- [Google APIs](https://developers.google.com) - Google OAuth integration
- [Microsoft Graph](https://docs.microsoft.com/graph) - Microsoft 365 integration


