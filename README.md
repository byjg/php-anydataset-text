# AnyDataset-Text

[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg.com-brightgreen.svg)](http://opensource.byjg.com)
[![Build Status](https://travis-ci.org/byjg/anydataset-text.svg?branch=master)](https://travis-ci.org/byjg/anydataset-text)


Text file abstraction dataset. Anydataset is an agnostic data source abstraction layer in PHP. 

See more about Anydataset [here](https://opensource.byjg.com/anydataset).

# Examples

## Text File Delimited

```php
<?php
$file = "Joao;Magalhaes
John;Doe
Jane;Smith";
    
$dataset = new \ByJG\AnyDataset\Text\TextFileDataset(
    $file,
    ["name", "surname"],
    \ByJG\AnyDataset\Text\TextFileDataset::CSVFILE
);
$iterator = $dataset->getIterator();

foreach ($iterator as $row) {
    echo $row->get('name');     // Print "Joao", "John", "Jane"
    echo $row->get('surname');  // Print "Magalhaes", "Doe", "Smith"
}
```

## Text File Fixed sized columns

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

## Read from remote url

`TextFileDataset` and `FixedTextFileDataset` support read file from remote http or https

# Install

Just type: `composer require "byjg/anydataset-text=4.0.*"`

# Running Unit tests

```php
vendor/bin/phpunit
```

----
[Open source ByJG](http://opensource.byjg.com)
