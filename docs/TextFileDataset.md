# TextFileDataset

The `TextFileDataset` class provides functionality to work with delimited text files, such as CSV files. It allows you to parse and iterate through text files where fields are separated by delimiters.

## Basic Usage

```php
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

- `TextFileDataset::CSVFILE` - A generic file definition that accepts both `,` and `;` as delimiters
- `TextFileDataset::CSVFILE_COMMA` - The standard CSV file format that accepts only `,` as delimiter
- `TextFileDataset::CSVFILE_SEMICOLON` - A CSV variation that accepts only `;` as delimiter

## Field Names

You can specify field names in two ways:

1. Explicitly define field names using the `withFields()` method:

```php
$dataset = \ByJG\AnyDataset\Text\TextFileDataset::getInstance($file)
    ->withFields(["name", "surname"])
    ->withFieldParser(\ByJG\AnyDataset\Text\TextFileDataset::CSVFILE);
```

2. Use the first line of the file as field names by omitting the `withFields()` method:

```php
$dataset = \ByJG\AnyDataset\Text\TextFileDataset::getInstance($file)
    ->withFieldParser(\ByJG\AnyDataset\Text\TextFileDataset::CSVFILE);
```

## Remote Files

`TextFileDataset` supports reading files from remote HTTP or HTTPS URLs:

```php
$dataset = \ByJG\AnyDataset\Text\TextFileDataset::getInstance("https://example.com/data.csv")
    ->withFieldParser(\ByJG\AnyDataset\Text\TextFileDataset::CSVFILE);
```

## Methods

- `getInstance($file)` - Creates a new instance with the specified file
- `withFields(array $fields)` - Sets the field names
- `withFieldParser($pattern)` - Sets the field parser pattern
- `getIterator()` - Returns an iterator for the dataset 