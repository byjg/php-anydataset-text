<?php

namespace ByJG\AnyDataset\Text;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\RowArray;
use ByJG\Serializer\Exception\InvalidArgumentException;
use ByJG\Serializer\Reader\CsvReader;
use Generator;
use Override;

/**
 * The records of a CSV file (RFC 4180), read with a separator and an enclosure: a value in the enclosure may
 * contain the separator, line breaks and the enclosure doubled.
 */
class CsvFileIterator extends GenericIterator
{
    /**
     * @param resource $handle The file, after the header row when it has one
     * @param array $fields The name of each field, in order
     * @param string $separator
     * @param string $enclosure
     */
    public function __construct(
        protected $handle,
        protected array $fields,
        protected string $separator,
        protected string $enclosure
    ) {
    }

    #[Override]
    protected function rows(): Generator
    {
        return self::walk($this->handle, $this->fields, $this->separator, $this->enclosure);
    }

    /**
     * The rows of the file; the file is closed when they end or the iterator is destroyed
     *
     * @param resource $handle
     * @param array $fields
     * @param string $separator
     * @param string $enclosure
     * @return Generator
     * @throws InvalidArgumentException
     */
    private static function walk($handle, array $fields, string $separator, string $enclosure): Generator
    {
        try {
            foreach (CsvReader::records($handle, false, $separator, $enclosure) as $values) {
                $row = new RowArray();
                // A record with fewer values than the fields leaves them out; the extra values are ignored
                foreach (array_slice($values, 0, count($fields)) as $i => $value) {
                    $row->set($fields[$i], $value);
                }
                yield $row;
            }
        } finally {
            fclose($handle);
        }
    }
}
