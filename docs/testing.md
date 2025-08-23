# Test Structure

This document outlines the organization of tests in the Turahe Core package.

## Test Organization

### Unit Tests (`tests/Unit/`)

Unit tests focus on testing individual components in isolation, typically without external dependencies like databases or external services.

#### Model Tests
- **`SettingTest.php`** - Tests the `Setting` model's traits, methods, and behavior
- **`OrganizationModelTest.php`** - Tests the `Organization` model's traits, methods, and behavior  
- **`TaxonomyModelTest.php`** - Tests the `Taxonomy` model's traits, methods, and behavior
- **`TagModelTest.php`** - Tests the `Tag` model's traits, methods, and behavior
- **`OAuthAccountModelTest.php`** - Tests the `OAuthAccount` model's traits, methods, and behavior

#### Trait Tests
- **`HasConfigurablePrimaryKeyTest.php`** - Tests the configurable primary key trait functionality
- **`ModelsConfigurablePrimaryKeyTest.php`** - Tests that all models use the configurable primary key trait correctly

#### Service Tests
- **`ImageTest.php`** - Tests image processing services
- **`ImageSignatureTest.php`** - Tests image signature functionality
- **`HelpersTest.php`** - Tests helper functions

#### Mail Tests
- **`tests/Unit/Mail/`** - Tests for mail-related functionality

### Feature Tests (`tests/Feature/`)

Feature tests focus on testing the integration between components, typically involving databases, external services, or multiple components working together.

#### Repository Tests
- **`OrganizationRepositoryTest.php`** - Tests organization repository operations (CRUD, database interactions)
- **`TaxonomyRepositoryTest.php`** - Tests taxonomy repository operations (CRUD, database interactions)
- **`TagRepositoryTest.php`** - Tests tag repository operations (CRUD, database interactions)
- **`OAuthAccountRepositoryTest.php`** - Tests OAuth account repository operations (CRUD, database interactions)

#### Database Tests
- **`MigrationTest.php`** - Tests database migrations and schema
- **`OrganizationSeederTest.php`** - Tests database seeding functionality

#### Model Integration Tests
- **`UserModelTest.php`** - Tests user model integration with other components

## Test Categories

### Unit Tests
- **Purpose**: Test individual components in isolation
- **Scope**: Single class, method, or trait
- **Dependencies**: Minimal external dependencies
- **Speed**: Fast execution
- **Examples**: 
  - Testing model traits work correctly
  - Testing individual methods return expected values
  - Testing model configurations and attributes
  - Testing configurable primary key behavior

### Feature Tests
- **Purpose**: Test component integration and workflows
- **Scope**: Multiple components working together
- **Dependencies**: Database, factories, repositories
- **Speed**: Slower execution due to database operations
- **Examples**:
  - Testing repository CRUD operations
  - Testing database migrations
  - Testing model relationships and database queries
  - Testing seeding and data creation workflows

## Running Tests

### Run All Tests
```bash
./vendor/bin/phpunit
```

### Run Unit Tests Only
```bash
./vendor/bin/phpunit tests/Unit/
```

### Run Feature Tests Only
```bash
./vendor/bin/phpunit tests/Feature/
```

### Run Specific Test File
```bash
./vendor/bin/phpunit tests/Unit/SettingTest.php
```

### Run Tests with Filter
```bash
./vendor/bin/phpunit --filter test_setting_uses_ulids
```

## Test Configuration

- **Database**: Uses in-memory SQLite for fast, isolated testing
- **Cache**: Uses array cache to avoid external dependencies
- **Framework**: Uses Orchestra Testbench for Laravel package testing
- **PHPUnit**: Configured via `phpunit.xml`

## Best Practices

1. **Unit Tests**: Keep focused on single responsibility, avoid database operations
2. **Feature Tests**: Use factories for test data, test real workflows
3. **Naming**: Use descriptive test method names that explain the expected behavior
4. **Assertions**: Make specific assertions that clearly indicate what should happen
5. **Setup**: Use `setUp()` method for common test preparation
6. **Cleanup**: Ensure tests clean up after themselves to avoid interference
