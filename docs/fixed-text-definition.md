---
sidebar_position: 3
---

# FixedTextDefinition

The `FixedTextDefinition` class is used to define the structure of fixed-width text files. It specifies the name, position, length, and type of each field in the file.

## Basic Usage

```php title="Creating a field definition"
<?php
$definition = new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition(
    'fieldName',  // The field name
    0,            // The start position (0-based)
    10,           // The length of the field
    \ByJG\AnyDataset\Text\Definition\TextTypeEnum::STRING  // The field type
);
```

## Constructor Parameters

The `FixedTextDefinition` constructor accepts the following parameters:

```php
$definition = new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition(
    $fieldName,      // The field name (string)
    $startPos,       // The start position of this field in the row (int, 0-based)
    $length,         // The number of characters of the field content (int)
    $type,           // (optional) The type of the field content: TextTypeEnum::STRING (default) or TextTypeEnum::NUMBER
    $requiredValue,  // (optional) An array of valid values. E.g. ['Y', 'N']
    $subTypes        // (optional) An associative array of FixedTextDefinition for conditional field parsing
);
```

## Field Types

The `TextTypeEnum` class defines the available field types:

- `TextTypeEnum::STRING` - String type (default)
- `TextTypeEnum::NUMBER` - Numeric type

## Required Values

You can specify an array of valid values for a field to validate the data:

```php
$definition = new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition(
    'status',
    10,
    1,
    \ByJG\AnyDataset\Text\Definition\TextTypeEnum::STRING,
    ['A', 'I', 'P']  // Only 'A', 'I', or 'P' are valid values
);
```

## Conditional Field Parsing

You can define different field structures based on the value of a field using the `$subTypes` parameter:

```php
$definition = new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition(
    'recordType',
    0,
    1,
    \ByJG\AnyDataset\Text\Definition\TextTypeEnum::STRING,
    null,
    [
        "H" => [  // If recordType is "H" (Header), use these field definitions
            new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('date', 1, 8, \ByJG\AnyDataset\Text\Definition\TextTypeEnum::STRING),
            new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('batch', 9, 5, \ByJG\AnyDataset\Text\Definition\TextTypeEnum::NUMBER),
        ],
        "D" => [  // If recordType is "D" (Detail), use these field definitions
            new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('id', 1, 5, \ByJG\AnyDataset\Text\Definition\TextTypeEnum::NUMBER),
            new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('name', 6, 20, \ByJG\AnyDataset\Text\Definition\TextTypeEnum::STRING),
        ]
    ]
);
```

## Properties

All properties of the `FixedTextDefinition` class are public and can be accessed directly:

- `fieldName` - The field name (string)
- `startPos` - The start position (int)
- `length` - The field length (int)
- `type` - The field type (TextTypeEnum)
- `requiredValue` - The array of required values (array|null)
- `subTypes` - The sub-type definitions (array|null) 