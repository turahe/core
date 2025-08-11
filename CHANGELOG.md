# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.3.0] - 2025-08-11

### Added
- **Comprehensive Docker Testing Infrastructure**: Complete rewrite of testing setup with Docker containerization
- **Cross-Platform Test Scripts**: PowerShell, Bash, and Windows Batch scripts for all platforms
- **Test Suite Organization**: Organized tests into `unit` and `feature` suites for better test management
- **Automatic Container Cleanup**: All scripts now automatically run `docker compose down` after test completion
- **SQLite Support for Unit Tests**: Fast, isolated unit testing without external database dependencies
- **Environment Configuration**: Uses `.env` files for flexible test configuration

### New Scripts
- `docker/run-phpunit.ps1` - PowerShell script for fast unit testing
- `docker/run-phpunit.sh` - Bash script for fast unit testing on Linux/macOS
- `docker/run-phpunit.bat` - Windows Batch script for fast unit testing
- `docker/run-tests.ps1` - PowerShell script for full integration testing
- `docker/run-tests.sh` - Bash script for full integration testing on Linux/macOS
- `docker/run-tests.bat` - Windows Batch script for full integration testing

### Enhanced Scripts
- `docker/run-tests.sh` - Full Docker Compose testing with MySQL and Redis
- `docker/run-tests.bat` - Full Docker Compose testing for Windows
- `docker/run-tests.ps1` - Enhanced PowerShell integration testing

### Infrastructure Improvements
- **Dockerfile Optimization**: Enhanced `docker/test/Dockerfile` with SQLite support and test-specific configuration
- **Test Environment Detection**: Automatic detection of database services vs. isolated testing
- **Database Schema Flexibility**: Nullable fields in test database schema for better test compatibility
- **Encryption Configuration**: Proper app key and cipher configuration for OAuth tests
- **Cache Driver Configuration**: Array-based caching for unit tests, Redis for integration tests

### Testing Enhancements
- **TestCase Base Class**: Enhanced `tests/TestCase.php` with database service detection
- **Userstamps Configuration**: Added `setUserstampsConfig()` method for flexible testing
- **Migration Testing**: Support for testing different userstamps configurations (ULID, UUID, BigInt)
- **Test Suite Management**: Eliminated duplicate test suite warnings
- **Database Constraints**: Fixed NOT NULL constraint violations in test database

### Documentation
- **Comprehensive README**: Complete documentation in `docker/README-TESTS.md`
- **Usage Examples**: Practical examples for development workflow and CI/CD pipelines
- **Troubleshooting Guide**: Common issues and solutions
- **Script Comparison**: Clear explanation of when to use each script type

### Performance Improvements
- **Fast Unit Testing**: Unit tests now run in ~4.5 seconds vs. previous slower database-dependent approach
- **Isolated Testing**: No external service dependencies for unit tests
- **Docker Layer Caching**: Optimized Docker builds with proper layer ordering
- **Memory-Efficient**: SQLite in-memory database for unit tests

### Platform Support
- **Windows**: PowerShell and Batch scripts with enhanced error handling
- **Linux/macOS**: Bash scripts with proper signal handling
- **Cross-Platform**: Consistent behavior across all operating systems
- **Docker Desktop**: Full support for Windows and macOS Docker environments

### Breaking Changes
- **Test Suite Organization**: Tests are now organized into `unit` and `feature` suites
- **Database Configuration**: Unit tests now use SQLite by default instead of MySQL
- **Script Behavior**: All scripts now automatically clean up containers after execution

### Migration Guide
- **Unit Tests**: Use `--testsuite=unit` for fast, isolated testing
- **Feature Tests**: Use `--testsuite=feature` for integration testing
- **Full Testing**: Use `run-tests.*` scripts for complete environment testing
- **Simple Testing**: Use `run-phpunit.*` scripts for quick unit test feedback

### Technical Details
- **PHP Version**: PHP 8.4 with all necessary extensions
- **Database**: SQLite 3 for unit tests, MySQL 8 for integration tests
- **Cache**: Array driver for unit tests, Redis for integration tests
- **Extensions**: PDO, MySQL, SQLite, Redis, GD, ZIP, and more
- **Composer**: Development dependencies included for testing

## [1.2.0] - Previous Release

### Added
- Initial package structure
- Basic models and concerns
- OAuth integration
- Google and Microsoft services

## [1.1.0] - Previous Release

### Added
- Core service provider
- Basic configuration
- Initial testing setup

## [1.0.0] - Initial Release

### Added
- Initial package release
- Basic functionality
- Core models and traits 
