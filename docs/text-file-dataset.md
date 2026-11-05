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

## CSV Files: `withSeparator()`

For a CSV file, use `withSeparator()`. The file is read as CSV records (RFC 4180): a value in the enclosure may
contain the separator, line breaks and the enclosure doubled (`""`).

```php
<?php
// users.csv:
//   id,name,note
//   1,"Doe, John","He said ""hi"""
// gives ['id' => '1', 'name' => 'Doe, John', 'note' => 'He said "hi"']
$dataset = \ByJG\AnyDataset\Text\TextFileDataset::getInstance("users.csv")
    ->withSeparator(",");

// Another separator and enclosure
$dataset = \ByJG\AnyDataset\Text\TextFileDataset::getInstance("users.csv")
    ->withSeparator(";", "'");
```

- `$separator` - the character between the fields (`,`, `;`, `|`, a tab...)
- `$enclosure` - the character that quotes a value; `"` by default
- The lines end in LF or CRLF. `withEofChar()` is not supported with `withSeparator()`: `getIterator()` throws
  `DatasetException`.
- A record with fewer values than the fields leaves the missing fields out; the extra values are ignored.
- `withSeparator()` and `withFieldParser()` replace each other: the last one called is used.

## Pre-defined Field Parsers

Without `withSeparator()`, each line is split by a regular expression (the field parser). It accepts several
delimiters at once and values quoted with `"` or `'`, but a value cannot contain a line break, and `""` inside a
value is not read as `"`.

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

:::note
Field names are always converted to lowercase, both those passed to `withFields()` and those read
from the first line. Use the lowercase name with `$row->get()`.
:::

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
- `withSeparator(string $separator, string $enclosure = '"')` - Reads the file as CSV records with that separator
  and enclosure, and returns the instance for method chaining
- `getIterator()` - Returns an iterator for the dataset 