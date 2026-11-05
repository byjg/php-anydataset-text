# Changelog - Version 7.0

> **Status: in development.** This document tracks changes landing on the `7.0` branch.
> Nothing here is released yet, and the contents may still change.

## Breaking Changes

### Enum cases renamed to PascalCase

Enum cases follow the ByJG standard and are written in PascalCase. `TextTypeEnum` is a pure
(non-backed) enum, so any code that names a case (or reads `->name`) has to be updated.

| Enum | Before (6.x) | After (7.0) |
|---|---|---|
| `TextTypeEnum` | `STRING`, `NUMBER` | `String`, `Number` |

### `FixedTextFileDataset::getIteratorHttp()` removed

The protected `getIteratorHttp()` method was removed (see Bug Fixes). Remote URLs now go
through `getIteratorFile()`; a subclass that overrode `getIteratorHttp()` must override
`getIteratorFile()` instead. This was renaned to `openStreamIterator()` 

### The iterators are generator based

`TextFileIterator` and `FixedTextFileIterator` follow byjg/anydataset 7.0 `GenericIterator`
(`IteratorAggregate`, forward only, a static `rows()`; see its changelog). The file is closed when the
rows end **or as soon as the iterator is dropped** (`first()`, a `foreach` with `break`).

- `TextFileIterator::parseLine()` is now `protected static parseLine(string $buffer, array $fields,
  string $fieldExpression)` (it was public and read the current line of the iterator), and `readLine()` is
  `protected static readLine($handle, string $eofChar)`.
- `FixedTextFileIterator::processBuffer()` is now `protected static`.
- `FixedTextFileIterator`: an error of a field definition (a required value, a subtype) is thrown while the
  rows are read, not by `getIterator()` (the first line is no longer read in the constructor).

## New Features

### CSV files: `TextFileDataset::withSeparator()`

`withSeparator($separator, $enclosure = '"')` reads the file as CSV records (RFC 4180) with byjg/serializer
`CsvReader`: a value in the enclosure may contain the separator, line breaks and the enclosure doubled
(`""`), which the field parser modes cannot read.

```php
// users.csv:  id,name,note
//             1,"Doe, John","He said ""hi"""
$dataset = TextFileDataset::getInstance('users.csv')->withSeparator(',');
// ['id' => '1', 'name' => 'Doe, John', 'note' => 'He said "hi"']

$dataset = TextFileDataset::getInstance('users.csv')->withSeparator(';', "'");
```

- Header names are lower-cased, and a record with fewer or more values than the fields is filled as far
  as it goes, as in the field parser modes.
- `withSeparator()` and `withFieldParser()` replace each other: the last one called is used.
- The lines end in LF or CRLF: `withEofChar()` with `withSeparator()` throws `DatasetException`.
- The field parser modes (`CSVFILE`, `CSVFILE_COMMA`, `CSVFILE_SEMICOLON`, `withEofChar()`) are unchanged.

**Documentation:** [TextFileDataset](docs/text-file-dataset.md)

## Performance

- Reading a text file is about 1.3x faster (200,000 lines with the field parser: ~441 ms -> ~344 ms;
  PHP 8.5, Xdebug off).

## Bug Fixes

- `FixedTextFileDataset` could not read remote files: it opened a raw socket on port 80
  (no TLS, so HTTPS never worked), rejected host names with a `-`, and did not strip the
  HTTP response headers. It now opens URLs with `fopen()` and PHP's http(s) stream wrapper,
  like `TextFileDataset`. A URL that cannot be opened throws `DatasetException`.

## Requirements

- PHP 8.3, 8.4, 8.5 and 8.6 are now supported: `"php": ">=8.3 <8.7"`.
  The previous `<8.6` upper bound excluded PHP 8.6, since `<8.6` is exclusive.

### ByJG dependencies

- `byjg/anydataset` is now `^7.0`.
- `byjg/serializer` `^7.0` is now required directly (it came only through `byjg/anydataset`).

While 7.0 is unreleased these resolve to `7.0.x-dev` from each component's
`7.0` branch, via `minimum-stability: dev` with `prefer-stable: true`.

## Toolchain

- PHPUnit updated to `^12.5`.
- Psalm is installed as `psalm/phar` instead of `vimeo/psalm`.

  `vimeo/psalm` lists the PHP versions it supports and no published release includes
  8.6, so as a dev dependency it made `composer install` fail on the 8.6 build job
  before any test ran. `psalm/phar` requires only `php ^8.2` and bundles its own
  dependencies, so it installs on every PHP version in the matrix and cannot conflict
  with the project's. Psalm itself still refuses to *run* on 8.6, which is why the
  Psalm job uses 8.5. `composer psalm` runs it.

- PHPUnit 13 is deliberately **not** used. It requires PHP `>=8.4.1`, breaking the
  8.3 floor.

## Continuous Integration

- The build matrix now includes PHP 8.6.
- The Psalm job runs on PHP 8.5.

## Housekeeping

- `phpunit.xml.dist` renamed to `phpunit.xml`.
- The documentation pages were renamed to kebab-case (`fixed-text-definition.md`, `fixed-text-file-dataset.md`, `formatters.md`, `text-file-dataset.md`), so their
  addresses on the documentation site change as well.
