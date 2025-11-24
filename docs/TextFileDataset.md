---
sidebar_position: 1
---

# TextFileDataset

The `TextFileDataset` class provides functionality to work with delimited text files, such as CSV files. It allows you to parse and iterate through text files where fields are separated by delimiters.

## Basic Usage

```php title="example.php"
<?php
$file = "example.csv";

$dataset = \ByJG\AnyDataset\Text\TextFileDataset::getInstance($file)
    ->withFields(["name", "surname"])
    ->withFieldParser(\ByJG\AnyDataset\Text\TextFileDataset::CSVFILE);
$iterator = $dataset->getIterator();

foreach ($iterator as $row) {
    echo $row->get('name');
    echo $row->get('surname');
}
```

## Pre-defined Field Parsers

The `TextFileDataset` class provides several pre-defined field parsers:

- `TextFileDataset::CSVFILE` - A generic file definition that accepts `|`, `,` and `;` as delimiters
- `TextFileDataset::CSVFILE_COMMA` - The standard CSV file format that accepts only `,` as delimiter
- `TextFileDataset::CSVFILE_SEMICOLON` - A CSV variation that accepts only `;` as delimiter

## Field Names

You can specify field names in two ways:

### 1. Explicitly define field names

Use the `withFields()` method to specify field names:

```php
$dataset = \ByJG\AnyDataset\Text\TextFileDataset::getInstance($file)
    ->withFields(["name", "surname"])
    ->withFieldParser(\ByJG\AnyDataset\Text\TextFileDataset::CSVFILE);
```

### 2. Use first line as header

Omit the `withFields()` method to use the first line of the file as field names:

```php
$dataset = \ByJG\AnyDataset\Text\TextFileDataset::getInstance($file)
    ->withFieldParser(\ByJG\AnyDataset\Text\TextFileDataset::CSVFILE);
```

## End of File Character

You can specify a custom end-of-line character if your file uses a non-standard line terminator:

```php
$dataset = \ByJG\AnyDataset\Text\TextFileDataset::getInstance($file)
    ->withFieldParser(\ByJG\AnyDataset\Text\TextFileDataset::CSVFILE)
    ->withEofChar("\r");
```

## Remote Files

`TextFileDataset` supports reading files from remote HTTP or HTTPS URLs:

```php
$dataset = \ByJG\AnyDataset\Text\TextFileDataset::getInstance("https://example.com/data.csv")
    ->withFieldParser(\ByJG\AnyDataset\Text\TextFileDataset::CSVFILE);
```

## Methods

- `getInstance(string $source)` - Static factory method that creates a new instance with the specified file
- `withFields(array $fields)` - Sets the field names and returns the instance for method chaining
- `withFieldParser(string $pattern)` - Sets the field parser pattern and returns the instance for method chaining
- `withEofChar(string $char)` - Sets the end-of-line character and returns the instance for method chaining
- `getIterator()` - Returns an iterator for the dataset 