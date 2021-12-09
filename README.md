# AnyDataset-Text

[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/anydataset-text/)
[![GitHub license](https://img.shields.io/github/license/byjg/anydataset-text.svg)](https://opensource.byjg.com/opensource/licensing.html)
[![GitHub release](https://img.shields.io/github/release/byjg/anydataset-text.svg)](https://github.com/byjg/anydataset-text/releases/)
[![Build Status](https://travis-ci.com/byjg/anydataset-text.svg?branch=master)](https://travis-ci.com/byjg/anydataset-text)


Text file abstraction dataset. Anydataset is an agnostic data source abstraction layer in PHP. 

See more about Anydataset [here](https://opensource.byjg.com/anydataset).

## Examples

### Text File Delimited

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

### Text File Delimited - Get field names from first line

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

```php
<?php
$file = "".
    "001JOAO   S1520\n".
    "002GILBERTS1621\n";

$fieldDefinition = [
    new \ByJG\AnyDataset\Text\Enum\FixedTextDefinition('id', 0, 3),
    new \ByJG\AnyDataset\Text\Enum\FixedTextDefinition('name', 3, 7),
    new \ByJG\AnyDataset\Text\Enum\FixedTextDefinition('enable', 10, 1, 'S|N'), // Required value --> S or N
    new \ByJG\AnyDataset\Text\Enum\FixedTextDefinition('code', 11, 4),
];

$dataset = new \ByJG\AnyDataset\Text\FixedTextFileDataset(
    $file,
    $fieldDefinition
);

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

$dataset = new \ByJG\AnyDataset\Text\FixedTextFileDataset(
    $file,
    $fieldDefinition
);

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

`TextFileDataset` and `FixedTextFileDataset` support read file from remote http or https

## Install

Just type: `composer require "byjg/anydataset-text=4.0.*"`

## Running Unit tests

```bash
vendor/bin/phpunit
```

----
[Open source ByJG](http://opensource.byjg.com)
