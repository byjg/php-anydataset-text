---
sidebar_position: 4
---

# Formatters

Formatters are used to output dataset content in specific formats. The AnyDataset-Text package provides two formatters:

- `CSVFormatter` - Outputs the content as a CSV file (field delimited)
- `FixedSizeColumnFormatter` - Outputs the content with columns defined by length

## CSVFormatter

The `CSVFormatter` class allows you to output dataset content as a CSV file with customizable delimiter, quote character, and quoting behavior.

### Basic Usage

```php title="Using CSVFormatter"
<?php
$iterator = $anydataset->getIterator();
$formatter = new \ByJG\AnyDataset\Text\Formatter\CSVFormatter($iterator);
$csvContent = $formatter->toText();
```

### Customization Options

```php
$formatter = new \ByJG\AnyDataset\Text\Formatter\CSVFormatter($iterator);

// Set the delimiter character (default: ,)
$formatter->setDelimiter(';');

// Set the quote character (default: ")
$formatter->setQuote("'");

// Set the quoting behavior
$formatter->setApplyQuote(\ByJG\AnyDataset\Text\Formatter\CSVFormatter::APPLY_QUOTE_ALWAYS);

// Control whether to output header row (default: true)
$formatter->setOutputHeader(false);

// Get the formatted text
$csvContent = $formatter->toText();
```

### Quoting Behaviors

The `CSVFormatter` supports several quoting behaviors:

- `CSVFormatter::APPLY_QUOTE_ALWAYS` - Apply quotes to all fields
- `CSVFormatter::APPLY_QUOTE_WHEN_REQUIRED` - Apply quotes only when required (default)
- `CSVFormatter::APPLY_QUOTE_ALL_STRINGS` - Apply quotes to all string fields
- `CSVFormatter::NEVER_APPLY_QUOTE` - Never apply quotes

## FixedSizeColumnFormatter

The `FixedSizeColumnFormatter` class allows you to output dataset content with fixed-width columns based on field definitions.

### Basic Usage

```php title="Using FixedSizeColumnFormatter"
<?php
$fieldDefinition = [
    new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('id', 0, 3, \ByJG\AnyDataset\Text\Definition\TextTypeEnum::NUMBER),
    new \ByJG\AnyDataset\Text\Definition\FixedTextDefinition('name', 3, 7, \ByJG\AnyDataset\Text\Definition\TextTypeEnum::STRING),
    // ... more field definitions
];

$iterator = $anydataset->getIterator();
$formatter = new \ByJG\AnyDataset\Text\Formatter\FixedSizeColumnFormatter($iterator, $fieldDefinition);
$formattedContent = $formatter->toText();
```

### Customization Options

```php
$formatter = new \ByJG\AnyDataset\Text\Formatter\FixedSizeColumnFormatter($iterator, $fieldDefinition);

// Set the padding character for number fields (default: 0)
$formatter->setPadNumber('0');

// Set the padding character for string fields (default: space)
$formatter->setPadString(' ');

// Get the formatted text
$formattedContent = $formatter->toText();
```

### Methods

#### CSVFormatter

- `__construct($anydataset, $delimiter = ",", $quote = '"', $applyQuote = 1)` - Constructor with customizable options
- `toText()` - Returns the formatted text as a string
- `setDelimiter($value)` - Sets the delimiter character
- `getDelimiter()` - Gets the delimiter character
- `setQuote($value)` - Sets the quote character
- `getQuote()` - Gets the quote character
- `setApplyQuote($value)` - Sets the quoting behavior
- `getApplyQuote()` - Gets the quoting behavior
- `setOutputHeader($outputHeader)` - Sets whether to output the header row
- `getOutputHeader()` - Gets whether to output the header row

#### FixedSizeColumnFormatter

- `__construct(GenericIterator|Row $anydataset, array $fieldDefinition)` - Constructor with required dataset and field definitions
- `toText()` - Returns the formatted text as a string
- `setPadNumber(string $padNumber)` - Sets the padding character for number fields
- `getPadNumber()` - Gets the padding character for number fields
- `setPadString(string $padString)` - Sets the padding character for string fields
- `getPadString()` - Gets the padding character for string fields 