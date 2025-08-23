# Turahe Core Scripts

This directory contains utility scripts for testing, coverage analysis, and local development.

## 📁 Available Scripts

### 🧪 Test Runners

#### `run-tests.bat` / `run-tests.sh`
**Purpose**: Run PHPUnit tests with coverage analysis using local PHP installation
**Usage**: 
- Windows: `scripts\run-tests.bat`
- Unix/Linux: `./scripts/run-tests.sh`

**Features**:
- Automatically installs dependencies if needed
- Generates comprehensive coverage reports
- Creates HTML, XML, and Clover coverage formats
- Shows test results with detailed output

#### `test-local.bat` / `test-local.sh`
**Purpose**: Run tests locally without Docker dependencies
**Usage**:
- Windows: `scripts\test-local.bat`
- Unix/Linux: `./scripts/test-local.sh`

**Features**:
- No Docker required
- Uses local PHP installation
- Automatic dependency installation
- Direct test execution

### 📊 Coverage Analysis

#### `coverage.bat` / `coverage.sh`
**Purpose**: Generate detailed coverage reports for all tests
**Usage**:
- Windows: `scripts\coverage.bat`
- Unix/Linux: `./scripts/coverage.sh`

**Features**:
- HTML coverage reports
- Clover XML for CI/CD integration
- Coverage XML for detailed analysis
- JUnit XML for test reporting

### 🔧 Setup & Configuration

#### `setup.bat` / `setup.sh`
**Purpose**: Setup development environment and dependencies
**Usage**:
- Windows: `scripts\setup.bat`
- Unix/Linux: `./scripts/setup.sh`

**Features**:
- Environment configuration
- Dependency installation
- Database setup
- Testing environment preparation

## 🚀 Quick Start

### For Local Testing (Recommended)
```bash
# Windows
scripts\test-local.bat

# Unix/Linux
./scripts/test-local.sh
```

### For Coverage Reports
```bash
# Windows
scripts\coverage.bat

# Unix/Linux
./scripts/coverage.sh
```

### For Environment Setup
```bash
# Windows
scripts\setup.bat

# Unix/Linux
./scripts/setup.sh
```

## 📋 Prerequisites

### Local Testing
- PHP 8.4+ with Xdebug extension
- Composer installed
- PHPUnit available via Composer
- SQLite support (for in-memory testing)
- Array cache driver (no external cache service required)

### Coverage Analysis
- Xdebug extension enabled
- Xdebug mode set to "coverage"
- Sufficient memory allocation

## 📊 Coverage Reports

After running tests with coverage, reports are generated in the `coverage/` directory:

- **HTML Report**: `coverage/html/index.html` - Interactive web-based coverage
- **Clover XML**: `coverage/clover.xml` - CI/CD integration format
- **Coverage XML**: `coverage/xml/` - Detailed coverage data
- **JUnit XML**: `coverage/junit.xml` - Test results for CI systems

## 🔧 Configuration

All scripts use the project's `phpunit.xml` configuration file. The testing environment is configured for:
- **Database**: SQLite in-memory for fast testing
- **Cache**: Array driver for testing (no external cache service)
- **Session**: Array driver for testing (no file system dependency)
- **Queue**: Sync driver for testing (no external queue service)

## 🐛 Troubleshooting

### Common Issues

1. **PHP not found**: Ensure PHP is in your system PATH
2. **Xdebug not available**: Install and enable Xdebug extension
3. **Composer not found**: Install Composer globally
4. **Coverage not generated**: Verify Xdebug is in coverage mode
5. **Memory issues**: Increase PHP memory limit for large test suites

### Getting Help

- Check the main project README for setup instructions
- Review the `phpunit.xml` configuration
- Ensure all dependencies are properly installed
- Check PHP and Xdebug configuration

## 📝 Script Maintenance

When updating scripts:
- Keep both Windows (.bat) and Unix (.sh) versions in sync
- Test on both platforms when possible
- Update this README when adding new scripts
- Maintain consistent naming conventions
- Focus on local development without external dependencies

## 🎯 Benefits of Local Testing

- **Faster execution**: No container startup time
- **Easier debugging**: Direct access to local environment
- **No network issues**: All dependencies local
- **Simpler setup**: No Docker installation required
- **Better IDE integration**: Direct file access
- **No external services**: Array cache, in-memory database, sync queues
- **Instant startup**: No service initialization delays
- **Consistent behavior**: Same environment across all platforms
