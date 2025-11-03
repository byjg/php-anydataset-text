---
sidebar_position: 2
---

# FixedTextFileDataset

The `FixedTextFileDataset` class provides functionality to work with fixed-width text files, where each field is defined by its position and length in the line.

## Basic Usage

```php title="example.php"
<?php
$file = "data.txt";

$fieldDefinition = [
    new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('id', 0, 3, \ByJG\AnyDataset\Text\Definition\TextTypeEnum::NUMBER),
    new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('name', 3, 7, \ByJG\AnyDataset\Text\Definition\TextTypeEnum::STRING),
    new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('enable', 10, 1, \ByJG\AnyDataset\Text\Definition\TextTypeEnum::STRING),
    new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('code', 11, 4, \ByJG\AnyDataset\Text\Definition\TextTypeEnum::NUMBER),
];

$dataset = \ByJG\AnyDataset\Text\FixedTextFileDataset::getInstance($file)
    ->withFieldDefinition($fieldDefinition);

$iterator = $dataset->getIterator();
foreach ($iterator as $row) {
    echo $row->get('id');
    echo $row->get('name');
    echo $row->get('enable');
    echo $row->get('code');
}
```

## Field Definition

The field definition is created using the `FixedTextDefinition` class with the following parameters:

```php
$definition = new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition(
    $fieldName,      // The field name
    $startPos,       // The start position of this field in the row (0-based)
    $length,         // The number of characters of the field content
    $type,           // (optional) The type of the field content: TextTypeEnum::STRING (default) or TextTypeEnum::NUMBER
    $requiredValue,  // (optional) An array of valid values. E.g. ['Y', 'N']
    $subTypes        // (optional) An associative array of FixedTextDefinition for conditional field parsing
);
```

## Required Values

You can specify required values for a field to validate the data:

```php
new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition(
    'enable', 
    10, 
    1, 
    \ByJG\AnyDataset\Text\Definition\TextTypeEnum::STRING, 
    ['S', 'N']  // Required values --> S or N
)
```

## Conditional Field Parsing

You can define different field structures based on the value of a field using the `$subTypes` parameter:

```php
new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition(
    'enable',
    10,
    1,
    \ByJG\AnyDataset\Text\Definition\TextTypeEnum::STRING,
    null,
    [
        "S" => [
            new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('first', 11, 1, \ByJG\AnyDataset\Text\Definition\TextTypeEnum::STRING),
            new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('second', 12, 3, \ByJG\AnyDataset\Text\Definition\TextTypeEnum::STRING),
        ],
        "N" => [
            new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('reason', 11, 4, \ByJG\AnyDataset\Text\Definition\TextTypeEnum::STRING),
        ]
    ]
)
```

## Remote Files

`FixedTextFileDataset` supports reading files from remote HTTP or HTTPS URLs:

```php
$dataset = \ByJG\AnyDataset\Text\FixedTextFileDataset::getInstance("https://example.com/data.txt")
    ->withFieldDefinition($fieldDefinition);
```

## Methods

- `getInstance($file)` - Static factory method that creates a new instance with the specified file
- `withFieldDefinition(array $fieldDefinition)` - Sets the field definition and returns the instance for method chaining
- `getIterator()` - Returns an iterator for the dataset 