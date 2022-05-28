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

```php
<?php
$file = "Joao;Magalhaes
John;Doe
Jane;Smith";
    
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

```php
<?php
$file = "firstname;lastname
John;Doe
Jane;Smith";
    
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
    new \ByJG\AnyDataset\Text\Enum\FixedTextDefinition('id', 0, 3, FixedTextDefinition::TYPE_NUMBER),
    new \ByJG\AnyDataset\Text\Enum\FixedTextDefinition('name', 3, 7m , FixedTextDefinition::TYPE_STRING),
    new \ByJG\AnyDataset\Text\Enum\FixedTextDefinition('enable', 10, 1, , FixedTextDefinition::TYPE_STRING, ['S', 'N']), // Required values --> S or N
    new \ByJG\AnyDataset\Text\Enum\FixedTextDefinition('code', 11, 4, , FixedTextDefinition::TYPE_NUMBER),
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
    new \ByJG\AnyDataset\Text\Enum\FixedTextDefinition('id', 0, 3),
    new \ByJG\AnyDataset\Text\Enum\FixedTextDefinition('name', 3, 7),
    new \ByJG\AnyDataset\Text\Enum\FixedTextDefinition(
        'enable',
        10,
        1,
        FixedTextDefinition::TYPE_STRING,
        null,
        [
            "S" => [
                new \ByJG\AnyDataset\Text\Enum\FixedTextDefinition('first', 11, 1),
                new \ByJG\AnyDataset\Text\Enum\FixedTextDefinition('second', 12, 3),
            ],
            "N" => [
                new \ByJG\AnyDataset\Text\Enum\FixedTextDefinition('reason', 11, 4),
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

## Install

Just type: `composer require "byjg/anydataset-text=4.2.*"`

## Running Unit tests

```bash
vendor/bin/phpunit
```

----
[Open source ByJG](http://opensource.byjg.com)
