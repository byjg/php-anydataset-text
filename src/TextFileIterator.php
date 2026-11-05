<?php

namespace ByJG\AnyDataset\Text;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\RowArray;
use Generator;

class TextFileIterator extends GenericIterator
{

    /** @var array */
    protected array $fields;

    /** @var non-empty-string */
    protected string $fieldexpression;

    /** @var string */
    protected string $eofChar;

    /** @var resource|closed-resource */
    protected $handle;

    /**
     * @access public
     * @param resource|closed-resource $handle
     * @param array $fields
     * @param non-empty-string $fieldExpression
     * @param string $eofChar
     */
    public function __construct($handle, array $fields, string $fieldExpression, string $eofChar)
    {
        $this->fields = $fields;
        $this->fieldexpression = $fieldExpression;
        $this->eofChar = $eofChar;
        $this->handle = $handle;
    }

    #[\Override]
    protected function rows(): Generator
    {
        return self::walk($this->handle, $this->fields, $this->fieldexpression, $this->eofChar);
    }

    /**
     * The rows of the file; the file is closed when they end or the iterator is destroyed
     *
     * @param resource|closed-resource $handle
     * @param non-empty-string $fieldExpression
     */
    private static function walk($handle, array $fields, string $fieldExpression, string $eofChar): Generator
    {
        if (!is_resource($handle)) {
            return;
        }
        try {
            while (($buffer = self::readLine($handle, $eofChar)) !== false) {
                if (trim($buffer) !== "") {
                    yield self::parseLine($buffer, $fields, $fieldExpression);
                }
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param resource $handle
     */
    protected static function readLine($handle, string $eofChar): string|false
    {
        if (empty($eofChar)) {
            return fgets($handle, 8192);
        }
        return stream_get_line($handle, 8192, $eofChar);
    }

    /**
     * @param non-empty-string $fieldExpression
     */
    protected static function parseLine(string $buffer, array $fields, string $fieldExpression): RowArray
    {
        $cleaned = preg_replace("/(\r?\n?)$/", "", $buffer);
        if ($cleaned === null) {
            return new RowArray();
        }

        $cols = preg_split($fieldExpression, $cleaned, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($cols === false) {
            return new RowArray();
        }

        $row = new RowArray();

        for ($i = 0; ($i < count($fields)) && ($i < count($cols)); $i++) {
            $column = $cols[$i];

            if (($i >= count($fields) - 1) || ($i >= count($cols) - 1)) {
                $cleaned = preg_replace("/(\r?\n?)$/", "", $column);
                $column = $cleaned ?? $column;
            }
            $cleaned = preg_replace("/^[\"'](.*)[\"']$/", "$1", $column);
            $column = $cleaned ?? $column;

            $row->set($fields[$i], $column);
        }

        return $row;
    }
}
