# AnyDataset-Text

[![Build Status](https://github.com/byjg/anydataset-text/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/byjg/anydataset-text/actions/workflows/phpunit.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/anydataset-text/)
[![GitHub license](https://img.shields.io/github/license/byjg/anydataset-text.svg)](https://opensource.byjg.com/opensource/licensing.html)
[![GitHub release](https://img.shields.io/github/release/byjg/anydataset-text.svg)](https://github.com/byjg/anydataset-text/releases/)

Text file abstraction dataset. Anydataset is an agnostic data source abstraction layer in PHP.

See more about Anydataset [here](https://opensource.byjg.com/anydataset).

## Examples

### Text File Delimited (CSV)

This type of files uses a delimiter to define each field. The most common formart is CSV but you can use your own based on a regular expression.
The class TextFileIterator has three constants with pre-defined formats:

- TextFileDataset::CSVFILE - A generic file definition. It accept both `,` and `;` as delimiter. 
- TextFileDataset::CSVFILE_COMMA - The CSV file. It accept only `,` as delimiter. 
 - TextFileDataset::CSVFILE_SEMICOLON - A CSV variation. It accept only `;` as delimiter. 

example1.csv
```csv
Joao;Magalhaes
John;Doe
Jane;Smith
```
example1.php
```php
<?php
$file = file_get_contents("example1.csv");
    
$dataset = \ByJG\AnyDataset\Text\TextFileDataset::getInstance($file)
    ->withFields(["name", "surname"])
    ->withFieldParser(\ByJG\AnyDataset\Text\TextFileDataset::CSVFILE);
$iterator = $dataset->getIterator();

foreach ($iterator as $row) {
    echo $row->get('name');     // Print "Joao", "John", "Jane"
    echo $row->get('surname');  // Print "Magalhaes", "Doe", "Smith"
}
```

### Text File Delimited (CSV) - Get field names from first line

example2.csv
```csv
firstname;lastname
John;Doe
Jane;Smith
```

example2.php
```php
<?php
$file = file_get_contents("example2.csv");
    
// If omit `withFields` will get the field names from first line of the file
$dataset = \ByJG\AnyDataset\Text\TextFileDataset::getInstance($file)
    ->withFieldParser(\ByJG\AnyDataset\Text\TextFileDataset::CSVFILE);
$iterator = $dataset->getIterator();

foreach ($iterator as $row) {
    echo $row->get('firstname');     // Print "John", "Jane"
    echo $row->get('lastname');  // Print "Doe", "Smith"
}
```

### Text File Fixed sized columns

This file has the field defined by it position on the line. It is necessary to define the name, type, position and field length for each field to to parse the file.
This definition also allows set up required values and sub-types based on a value.

The field definition is created by the enum `FixedTextDefinition` and it has the following fields:

```php
$definition = new FixedTextDefinition(
    $fieldName,      # The field name
    $startPos,       # The start position of this field in the row
    $length,         # The number of characteres of the field content
    $type,           # (optional) The type of the field content. FixedTextDefinition::TYPE_NUMBER or FixedTextDefinition::TYPE_STRING (default)
    $requiredValue,  # (optional) an array of valid values. E.g. ['Y', 'N']
    $subTypes = array(), # An associative array of FixedTextDefinition. If the value matches with the key of the associative array, then a sub set
                         # of FixedTextDefinition is processed. e.g.
                         # [
                         #    "Y" => [
                         #      new FixedTextDefinition(...),
                         #      new FixedTextDefinition(...),
                         #    ],
                         #    "N" => new FixedTextDefinition(...)
                         # ]
);
```

Example:

```php
<?php
$file = "".
    "001JOAO   S1520\n".
    "002GILBERTS1621\n";

$fieldDefinition = [
    new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('id', 0, 3, FixedTextDefinition::TYPE_NUMBER),
    new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('name', 3, 7, FixedTextDefinition::TYPE_STRING),
    new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('enable', 10, 1, FixedTextDefinition::TYPE_STRING, ['S', 'N']), // Required values --> S or N
    new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('code', 11, 4, FixedTextDefinition::TYPE_NUMBER),
];

$dataset = new \ByJG\AnyDataset\Text\FixedTextFileDataset($file)
    ->withFieldDefinition($fieldDefinition);

$iterator = $dataset->getIterator();
foreach ($iterator as $row) {
    echo $row->get('id');
    echo $row->get('name');
    echo $row->get('enabled');
    echo $row->get('code');
}
```

### Text File Fixed sized columns with conditional type of fields

```php
<?php
$file = "".
    "001JOAO   S1520\n".
    "002GILBERTS1621\n";

$fieldDefinition = [
    new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('id', 0, 3),
    new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('name', 3, 7),
    new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition(
        'enable',
        10,
        1,
        FixedTextDefinition::TYPE_STRING,
        null,
        [
            "S" => [
                new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('first', 11, 1),
                new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('second', 12, 3),
            ],
            "N" => [
                new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('reason', 11, 4),
            ]
        ]
    ),
];

$dataset = new \ByJG\AnyDataset\Text\FixedTextFileDataset($file)
    ->withFieldDefinition($fieldDefinition);

$iterator = $dataset->getIterator();
foreach ($iterator as $row) {
    echo $row->get('id');
    echo $row->get('name');
    echo $row->get('enabled');
    echo $row->get('first');       // Not empty if `enabled` == "S"
    echo $row->get('second');      // Not empty if `enabled` == "S"
    echo $row->get('reason');      // Not empty if `enabled` == "N"
}
```

### Read from remote url

Both `TextFileDataset` and `FixedTextFileDataset` support read file from remote http or https

## Formatters

This package implements two formatters:

- CSVFormatter - output the content as CSV File (field delimited)
- FixedSizeColumnFormatter - output the content with columns defined by length.

[Click here](http://opensource.byjg.com/php/anydataset.html#formatters) for more information about formatters.

### CSVFormatter

```php
$formatter = new CSVFormatter($anydataset->getIterator());
$formatter->setDelimiter(string);  # Default: ,
$formatter->setQuote(string);  # Default: "
$formatter->setApplyQuote(APPLY_QUOTE_ALWAYS | APPLY_QUOTE_WHEN_REQUIRED | APPLY_QUOTE_ALL_STRINGS | NEVER_APPLY_QUOTE); # Default: APPLY_QUOTE_WHEN_REQUIRED
$formatter->setOutputHeader(true|false);  # Default: true
$formatter->toText();
```

### FixedSizeColumnFormatter

```php
$fieldDefinition = [ ... ];  # See above about field defintion

$formatter = new FixedSizeColumnFormatter($anydataset->getIterator(), $fieldDefinition);
$formatter->setPadNumner(string);  # Default: 0
$formatter->setPadString(string);  # Default: space character
$formatter->toText();
```

## Install

```
composer require "byjg/anydataset-text"
```

## Running Unit tests

```bash
vendor/bin/phpunit
```

## Dependencies

```mermaid
flowchart TD
    byjg/anydataset-text --> byjg/anydataset
```

----
[Open source ByJG](http://opensource.byjg.com)