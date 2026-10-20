# Changelog - Version 6.0

## Overview

Version 6.0 brings improved type safety, enhanced validation, better error handling, and comprehensive documentation to the Text File Abstraction Dataset library.

## New Features

### Enhanced Type Safety
- Improved type declarations throughout the codebase for better IDE support and static analysis
- Added proper return type hints and parameter types to all methods
- Enhanced PHPStan/Psalm compatibility with stricter type checking

### Better Validation and Error Handling
- Added URL validation in `FixedTextFileDataset` to prevent invalid URL formats
- Added field definition validation to ensure definitions are properly set before iteration
- Improved error messages for invalid subtype configurations
- Enhanced buffer processing with better edge case handling

### Comprehensive Documentation
- Added dedicated documentation for `TextFileDataset` (docs/TextFileDataset.md)
- Added dedicated documentation for `FixedTextFileDataset` (docs/FixedTextFileDataset.md)
- Added dedicated documentation for `FixedTextDefinition` (docs/FixedTextDefinition.md)
- Added dedicated documentation for `Formatters` (docs/Formatters.md)
- Updated README with clearer examples and structure

### Improved Iterator Implementation
- Refactored iterators to use modern PHP Iterator interface methods
- Changed from `moveNext()/hasNext()` pattern to standard `current()/next()/valid()` pattern
- Better resource management with proper handle cleanup
- Enhanced iteration state management

### Development Experience
- Added composer scripts for testing and psalm analysis
- Updated GitHub Actions workflows with better PHP version matrix
- Added PHPStorm run configurations (.run/PHPUnit.run.xml, .run/psalm.run.xml)
- Updated Gitpod configuration for better development experience

## Breaking Changes

| Area | Before (5.x) | After (6.x) | Description |
|------|--------------|-------------|-------------|
| **PHP Version** | PHP 8.1 - 8.3 | PHP 8.3 - 8.5 | Minimum PHP version increased to 8.3 |
| **Dependencies** | `byjg/anydataset: ^5.0` | `byjg/anydataset: ^6.0` | Updated to anydataset 6.0 |
| **PHPUnit** | PHPUnit 9.6 | PHPUnit 10.5 or 11.5 | Updated to modern PHPUnit versions |
| **Psalm** | Psalm 6.0 | Psalm 5.9 or 6.13 | Expanded Psalm version support |
| **Iterator Interface** | Used `moveNext()` and `hasNext()` methods | Uses standard PHP Iterator methods (`current()`, `next()`, `valid()`) | Iterators now follow standard PHP Iterator pattern |
| **Row Class** | Used `Row` class | Uses `RowArray` and `RowInterface` | More explicit row type handling |
| **Null Handling** | Less strict null checks | Stricter null validation with proper type hints | Better type safety may require code adjustments |
| **Exception Handling** | Less validation | Added `InvalidArgumentException` for missing field definitions | Code must ensure field definitions are set before iteration |

## Path to Upgrade from 5.x to 6.x

### Step 1: Update System Requirements
Ensure your system meets the new requirements:
- Upgrade to PHP 8.3 or higher
- Update Composer dependencies

### Step 2: Update composer.json
```json
{
  "require": {
    "php": ">=8.3",
    "byjg/anydataset-text": "^6.0"
  }
}
```

### Step 3: Run Composer Update
```bash
composer update byjg/anydataset-text
```

This will automatically update the `byjg/anydataset` dependency to version 6.0.

### Step 4: Update Iterator Usage (If Using Custom Code)

If you extended the iterator classes or called iterator methods directly:

**Before (5.x):**
```php
$iterator = $dataset->getIterator();
while ($iterator->hasNext()) {
    $row = $iterator->moveNext();
    // Process row
}
```

**After (6.x):**
```php
$iterator = $dataset->getIterator();
foreach ($iterator as $row) {
    // Process row
}
// Or using standard Iterator methods:
while ($iterator->valid()) {
    $row = $iterator->current();
    // Process row
    $iterator->next();
}
```

### Step 5: Add Field Definition Validation

Ensure field definitions are set before calling `getIterator()`:

**Before (5.x):**
```php
$dataset = new FixedTextFileDataset($file);
// May have worked without setting field definition
$iterator = $dataset->getIterator();
```

**After (6.x):**
```php
$dataset = new FixedTextFileDataset($file);
$dataset->withFieldDefinition($fieldDefinition); // Required!
$iterator = $dataset->getIterator();
```

### Step 6: Update Type Hints (If Applicable)

If you have type hints referencing iterator classes:

```php
// Before
function processIterator(GenericIterator $iterator) { }

// After - no change needed for basic usage, but be aware of stricter types
function processIterator(GenericIterator $iterator): void { }
```

### Step 7: Test Your Application

Run your test suite to ensure everything works correctly:

```bash
vendor/bin/phpunit
```

### Step 8: Update Static Analysis (If Used)

If you use Psalm or PHPStan, you may need to update your configuration to handle the stricter type declarations:

```bash
vendor/bin/psalm
```

## Bug Fixes

- Fixed CSV formatter handling of edge cases with quotes and delimiters
- Fixed resource handle cleanup in iterators
- Fixed subtype key casting issues in fixed-width file parsing
- Improved preg_split error handling with proper false checks

## Additional Notes

- The library maintains backward compatibility for most common use cases (simple foreach iteration)
- Breaking changes primarily affect direct Iterator API usage and PHP version requirements
- All changes improve type safety and reduce potential runtime errors
- The migration path is straightforward for standard usage patterns
