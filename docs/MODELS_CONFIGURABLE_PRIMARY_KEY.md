# Models Configurable Primary Key Support

## Overview

All models in the Turahe Core package now support configurable primary key types based on the `userstamps.users_table_column_type` configuration setting. This allows you to easily switch between different primary key strategies without modifying the model code.

**Key Feature**: The `HasConfigurablePrimaryKey` trait now provides **actual primary key behavior**, not just configuration methods. It dynamically implements the appropriate primary key logic based on your configuration.

## Supported Primary Key Types

### 1. **bigincrements** (Auto-incrementing Big Integers)
- **Database Type**: `BIGINT UNSIGNED AUTO_INCREMENT`
- **Laravel Behavior**: Standard auto-incrementing primary keys
- **Use Case**: Traditional relational database primary keys with large range
- **Implementation**: Database handles ID generation automatically

### 2. **ulid** (Universally Unique Lexicographically Sortable Identifier)
- **Database Type**: `CHAR(26)` or `VARCHAR(26)`
- **Laravel Behavior**: ULID-based primary keys with automatic generation
- **Use Case**: Distributed systems, sortable unique identifiers
- **Default**: This is the default configuration
- **Implementation**: Automatically generates ULIDs on model creation

### 3. **uuid** (Universally Unique Identifier)
- **Database Type**: `CHAR(36)` or `VARCHAR(36)`
- **Laravel Behavior**: UUID-based primary keys with automatic generation
- **Use Case**: Distributed systems, unique identifiers without ordering requirements
- **Implementation**: Automatically generates UUIDs on model creation

## Configuration

Set the `userstamps.users_table_column_type` configuration value in your Laravel application:

```php
// config/userstamps.php
return [
    'users_table_column_type' => 'ulid', // or 'bigincrements' or 'uuid'
];
```

Or via environment variable:

```env
USERSTAMPS_USERS_TABLE_COLUMN_TYPE=ulid
```

## Models with Configurable Primary Keys

The following models now support configurable primary keys:

- **`Setting`** - Application settings model
- **`Organization`** - Organization/company model
- **`Taxonomy`** - Hierarchical taxonomy model
- **`Tag`** - Tagging model
- **`OAuthAccount`** - OAuth account model

## Implementation Details

### Trait Usage

All models use the `HasConfigurablePrimaryKey` trait which provides **complete primary key functionality**:

```php
use Turahe\Core\Concerns\HasConfigurablePrimaryKey;

class YourModel extends Model
{
    use HasConfigurablePrimaryKey;
    // ... other traits
}
```

**No need to import `HasUlids` or `HasUuids`** - the trait handles everything dynamically!

### Available Methods

The trait provides these methods for checking and using the current configuration:

```php
// Get the current configuration type
$model->getPrimaryKeyType(); // Returns: 'ulid', 'uuid', or 'bigincrements'

// Check if using unique string IDs
$model->shouldUseUniqueIds(); // Returns: true for 'ulid'/'uuid', false for 'bigincrements'

// Check if using auto-incrementing
$model->shouldUseIncrementing(); // Returns: true for 'bigincrements', false for 'ulid'/'uuid'

// Get the configured key type
$model->getConfiguredKeyType(); // Returns: 'string' for 'ulid'/'uuid', 'int' for 'bigincrements'

// Specific type checks
$model->usesBigIncrements(); // Returns: true only for 'bigincrements'
$model->usesUlids(); // Returns: true only for 'ulid'
$model->usesUuids(); // Returns: true only for 'uuid'

// Laravel compatibility methods
$model->getKeyType(); // Returns the appropriate key type for Laravel
$model->getIncrementing(); // Returns the appropriate incrementing status for Laravel

// ID generation methods
$model->newUniqueId(); // Generates appropriate ID based on configuration
$model->uniqueIds(); // Returns array of unique ID columns
```

## How It Works

### Dynamic Behavior

The trait automatically adapts its behavior based on your configuration:

1. **ULID Configuration**: Automatically generates ULIDs on model creation
2. **UUID Configuration**: Automatically generates UUIDs on model creation  
3. **BigIncrements Configuration**: Lets the database handle ID generation

### Automatic ID Generation

When you create a new model instance, the trait automatically:

```php
// For ULID configuration
$setting = new Setting(); // ID will be automatically generated as ULID

// For UUID configuration  
$setting = new Setting(); // ID will be automatically generated as UUID

// For bigincrements configuration
$setting = new Setting(); // No ID generation - database will auto-increment
```

### Laravel Integration

The trait seamlessly integrates with Laravel's Eloquent ORM:

- **`getKeyType()`**: Returns the correct key type for Laravel's query builder
- **`getIncrementing()`**: Returns the correct incrementing status for Laravel
- **`newUniqueId()`**: Provides the same interface as `HasUlids`/`HasUuids`
- **`uniqueIds()`**: Provides the same interface as `HasUlids`/`HasUuids`

## Migration Compatibility

The existing migrations already support all three primary key types. They dynamically adjust the schema based on the configuration:

```php
// Example from migrations
if (config('userstamps.users_table_column_type') === 'bigincrements') {
    $table->bigIncrements('id');
} elseif (config('userstamps.users_table_column_type') === 'ulid') {
    $table->ulid('id')->primary();
} elseif (config('userstamps.users_table_column_type') === 'uuid') {
    $table->uuid('id')->primary();
}
```

## Testing

The implementation includes comprehensive tests to ensure all models work correctly with different configurations:

```bash
# Test the trait itself
./vendor/bin/phpunit tests/Unit/Concerns/HasConfigurablePrimaryKeyTest.php

# Test that all models use the trait
./vendor/bin/phpunit tests/Unit/ModelsConfigurablePrimaryKeyTest.php

# Test individual models
./vendor/bin/phpunit tests/Unit/SettingTest.php
```

## Benefits

1. **True Flexibility**: Actually switch between primary key strategies at runtime
2. **No Hardcoded Traits**: Models automatically adapt to configuration
3. **Laravel Compatibility**: Seamlessly integrates with Eloquent ORM
4. **Automatic ID Generation**: IDs are generated automatically based on configuration
5. **Migration Support**: Existing migrations work with all types
6. **Runtime Configuration**: Change behavior without code changes
7. **Backward Compatibility**: Defaults to ULID behavior

## Example Usage

### Checking Primary Key Type in Code

```php
$organization = new Organization();

if ($organization->usesUlids()) {
    // Handle ULID-specific logic
    $id = $organization->newUniqueId(); // Generates ULID
} elseif ($organization->usesUuids()) {
    // Handle UUID-specific logic
    $id = $organization->newUniqueId(); // Generates UUID
} else {
    // Handle auto-incrementing logic
    // ID will be set by database
}
```

### Dynamic Configuration

```php
// Switch to bigincrements for testing
config(['userstamps.users_table_column_type' => 'bigincrements']);

$model = new Setting();
$this->assertTrue($model->shouldUseIncrementing());
$this->assertFalse($model->shouldUseUniqueIds());

// Switch back to ULID
config(['userstamps.users_table_column_type' => 'ulid']);
```

### Automatic ID Generation

```php
// With ULID configuration
config(['userstamps.users_table_column_type' => 'ulid']);
$setting = new Setting();
$setting->save(); // ID automatically generated as ULID

// With UUID configuration
config(['userstamps.users_table_column_type' => 'uuid']);
$setting = new Setting();
$setting->save(); // ID automatically generated as UUID

// With bigincrements configuration
config(['userstamps.users_table_column_type' => 'bigincrements']);
$setting = new Setting();
$setting->save(); // Database auto-increments ID
```

## Migration Strategy

When switching between primary key types:

1. **ULID to BigIncrements**: Requires data migration and schema changes
2. **ULID to UUID**: Requires data migration and schema changes
3. **BigIncrements to ULID/UUID**: Requires data migration and schema changes

**Note**: Always backup your data before changing primary key types in production.

## Future Enhancements

Potential future improvements:

- Support for additional primary key types (e.g., Snowflake IDs)
- Automatic migration generation for type changes
- Validation of configuration values
- Performance optimizations for specific types
- Integration with Laravel's model factories

## Summary

The new `HasConfigurablePrimaryKey` trait provides **true configurability** by:

1. **Removing hardcoded traits** (`HasUlids`, `HasUuids`)
2. **Implementing dynamic behavior** based on configuration
3. **Providing automatic ID generation** for ULID and UUID configurations
4. **Maintaining Laravel compatibility** with standard Eloquent methods
5. **Supporting runtime configuration changes** without code modifications

This approach gives you the flexibility to switch between primary key strategies while maintaining clean, maintainable code.
