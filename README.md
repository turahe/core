# Turahe Core

[![PHP Composer](https://github.com/turahe/core/actions/workflows/php.yml/badge.svg)](https://github.com/turahe/core/actions/workflows/php.yml)
[![CI with Docker](https://github.com/turahe/core/actions/workflows/ci-docker.yml/badge.svg)](https://github.com/turahe/core/actions/workflows/ci-docker.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-10%2B-red.svg)](https://laravel.com)
[![Packagist](https://img.shields.io/packagist/v/turahe/core.svg)](https://packagist.org/packages/turahe/core)
[![Packagist Downloads](https://img.shields.io/packagist/dt/turahe/core.svg)](https://packagist.org/packages/turahe/core)

A comprehensive Laravel package providing core functionality for modern web applications including image processing, OAuth integration, taxonomy management, organization handling, and utility helpers.

## 🚀 Features

### 📸 Image Processing
- **ImgProxy Integration**: Secure image URL signing and processing
- **Image Optimization**: Automatic resizing, cropping, and format conversion
- **Preset Support**: Predefined image processing configurations
- **Signature Verification**: Secure image URL validation

### 🔐 OAuth Integration
- **Google OAuth**: Complete Google API integration with Calendar, Gmail, and Drive
- **Microsoft Graph**: Full Microsoft Graph API support for Office 365 services
- **Token Management**: Automatic token refresh and storage
- **Resource Owners**: Custom resource owner implementations

### 🏷️ Taxonomy & Tagging
- **Hierarchical Taxonomies**: Nested set implementation for complex categorization
- **Flexible Tagging**: Polymorphic tagging system for any model
- **Slug Generation**: Automatic slug creation with Spatie Laravel Sluggable
- **Sortable Content**: Drag-and-drop ordering with Spatie Eloquent Sortable

### 🏢 Organization Management
- **Multi-tenant Support**: Organization-based data isolation
- **Role-based Access**: Member, Admin, and Owner roles
- **Hierarchical Structure**: Nested organization trees
- **User Associations**: Flexible user-organization relationships

### 📧 Email Services
- **Gmail Integration**: Full Gmail API support with message handling
- **Microsoft Graph Email**: Outlook/Exchange email integration
- **Attachment Processing**: Automatic email attachment handling
- **Message Composition**: Rich email composition features

### 🛠️ Utility Helpers
- **Currency Formatting**: `format_currency()` for consistent money display
- **Name Aliases**: `name_alias()` for user-friendly name display
- **Text Cleaning**: `clean()` for sanitized text output
- **Acronym Generation**: `acronym()` for abbreviation creation
- **Image URLs**: `imgProxy()` and `imgProxyPreset()` for secure image URLs
- **Percentage Calculations**: `calculate_percentage()` for mathematical operations
- **Phone Parsing**: `parse_phone()` for international phone number handling

## 📦 Installation

### Requirements
- PHP 8.3 or higher
- Laravel 10
- SQLite (for testing)
- Redis (optional, for caching)

### Via Composer
```bash
composer require turahe/core
```

### Publish Configuration
```bash
php artisan vendor:publish --provider="Turahe\Core\CoreServiceProvider"
```

### Run Migrations
```bash
php artisan migrate
```

## 📚 Documentation

For comprehensive documentation, guides, and technical details, visit the [docs/](docs/) directory:

- **[📖 Documentation Index](docs/README.md)** - Complete documentation overview
- **[🧪 Testing Guide](docs/TESTING_SUMMARY.md)** - Testing setup and guidelines
- **[🧪 Testing Guide](docs/TESTING_SUMMARY.md)** - Testing setup and guidelines
- **[🔧 Scripts & Tools](scripts/README.md)** - Utility scripts and automation

## ⚙️ Configuration

### Environment Variables
```env
# Database Configuration
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Redis Configuration (Optional)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Core Package Configuration
CORE_TABLE_USE_TIMESTAMPS=false
USERSTAMPS_USERS_TABLE_COLUMN_TYPE=ulid
APP_KEY=your-app-key

# Table Names (Customizable)
CORE_TABLE_SETTINGS=settings
CORE_TABLE_ORGANIZATIONS=organizations
CORE_TABLE_MODEL_HAS_ORGANIZATION=model_has_organization
CORE_TABLE_TAXONOMIES=taxonomies
CORE_TABLE_MODEL_HAS_TAXONOMIES=model_has_taxonomies
CORE_TABLE_TAGS=tags
CORE_TABLE_TAGGABLES=taggables
CORE_TABLE_OAUTH_ACCOUNTS=oauth_accounts

# Cache Configuration
CORE_CACHE_ENABLED=true
CORE_CACHE_SETTINGS_TTL=3600
```

### Google OAuth Setup
```env
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=your-redirect-uri
```

### Microsoft Graph Setup
```env
MSGRAPH_CLIENT_ID=your-ms-client-id
MSGRAPH_CLIENT_SECRET=your-ms-client-secret
MSGRAPH_REDIRECT_URI=your-redirect-uri
```

### ImgProxy Configuration
```env
IMGPROXY_URL=your-imgproxy-url
IMGPROXY_KEY=your-imgproxy-key
IMGPROXY_SALT=your-imgproxy-salt
```

## 🔧 Usage

### Service Providers
The package automatically registers the `CoreServiceProvider`. You can also manually register it in `config/app.php`:

```php
'providers' => [
    // ...
    Turahe\Core\CoreServiceProvider::class,
],
```

### Facades
The package provides convenient facades for Google and Microsoft services:

```php
use Turahe\Core\Facades\Google;
use Turahe\Core\Facades\MsGraph;

// Google Services
Google::calendar()->listEvents();
Google::gmail()->sendMessage($message);

// Microsoft Graph Services
MsGraph::calendar()->getEvents();
MsGraph::mail()->sendMessage($message);
```

### Helper Functions
```php
// Currency formatting
echo format_currency(1234.56, 'USD'); // $1,234.56

// Name aliases
echo name_alias('John Doe'); // J. Doe

// Text cleaning
echo clean('<script>alert("xss")</script>'); // alert("xss")

// Acronyms
echo acronym('World Health Organization'); // WHO

// Image URLs
echo imgProxy('https://example.com/image.jpg', 800, 600);
echo imgProxyPreset('https://example.com/image.jpg', 'thumbnail');

// Percentage calculations
echo calculate_percentage(25, 100); // 25.0

// Phone parsing
echo parse_phone('+1-555-123-4567'); // +15551234567
```

### Models and Traits

#### HasOrganization Trait
```php
use Turahe\Core\Concerns\HasOrganization;

class User extends Authenticatable
{
    use HasOrganization;
    
    // Your model implementation
}

// Usage
$user = User::find(1);
$user->organizations()->attach($organizationId, ['role' => 'MEMBER']);
$user->managedOrganizations; // Organizations where user is admin/owner
```

#### HasSettings Trait
```php
use Turahe\Core\Concerns\HasSettings;

class User extends Authenticatable
{
    use HasSettings;
    
    // Your model implementation
}

// Usage
$user = User::find(1);
$user->updateSetting('theme', 'dark');
$user->getSetting('theme', 'light'); // Returns 'dark' or default 'light'
$user->allSettings(); // Get all settings
```

#### HasTaxonomies Trait
```php
use Turahe\Core\Concerns\HasTaxonomies;

class Post extends Model
{
    use HasTaxonomies;
    
    // Your model implementation
}

// Usage
$post = Post::find(1);
$post->taxonomies()->attach($categoryId);
$post->taxonomies; // Get all taxonomies
```

#### HasTags Trait
```php
use Turahe\Core\Concerns\HasTags;

class Post extends Model
{
    use HasTags;
    
    // Your model implementation
}

// Usage
$post = Post::find(1);
$post->tags()->attach($tagId);
$post->tags; // Get all tags
```

### Repositories
```php
use Turahe\Core\Repositories\OrganizationRepository;

$repository = app(OrganizationRepository::class);

// Get all organizations
$organizations = $repository->getOrganizations();

// Get organization by ID
$organization = $repository->getOrganization(1);

// Get organization by name
$organization = $repository->getOrganizationByName('Acme Corp');

// Create organization
$organization = $repository->createOrganization([
    'name' => 'New Organization',
    'slug' => 'new-organization',
    'type' => 'company'
]);
```

## 🧪 Testing

### Running Tests
```bash
# Run all tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Feature

# Run with coverage
vendor/bin/phpunit --coverage-html coverage-report
```

### Local Testing
```bash
# Run tests locally
vendor/bin/phpunit

# Run with coverage
vendor/bin/phpunit --coverage-html coverage-report
```

### Test Coverage
- **Unit Tests**: 196 tests with 418 assertions
- **Feature Tests**: Database integration and trait testing
- **Coverage**: Comprehensive coverage of all package features

## 🧪 Local Development

### Quick Start
```bash
# Install dependencies
composer install

# Run tests
vendor/bin/phpunit

# Generate coverage report
vendor/bin/phpunit --coverage-html coverage-report
```

### Testing Environment
- **PHP 8.4+**: Required for development
- **SQLite**: In-memory database for testing
- **Xdebug**: Required for coverage reports
- **Composer**: Dependency management

## 📊 CI/CD

The package includes comprehensive GitHub Actions workflows:

- **PHP Tests**: Multi-version PHP/Laravel matrix testing
- **Code Quality**: PHPStan, Psalm, Laravel Pint
- **Security**: Security checker and vulnerability scanning
- **Package Testing**: Installation and integration testing

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Setup
```bash
# Clone repository
git clone https://github.com/turahe/core.git
cd core

# Install dependencies
composer install

# Setup testing environment
cp .env.example .env
php artisan key:generate

# Run tests
vendor/bin/phpunit
```

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Nur Wachid** - [wachid@outlook.com](mailto:wachid@outlook.com)

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The web framework
- [Spatie](https://spatie.be) - Laravel packages
- [Google APIs](https://developers.google.com) - Google services
- [Microsoft Graph](https://docs.microsoft.com/graph) - Microsoft services
- [ImgProxy](https://imgproxy.net) - Image processing
- [Orchestra Testbench](https://github.com/orchestral/testbench) - Package testing

---

**Made with ❤️ for the Laravel community**


