<?php

namespace ByJG\AnyDataset\Text;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\RowArray;
use ReturnTypeWillChange;

class TextFileIterator extends GenericIterator
{

    /** @var array */
    protected array $fields;

    /** @var string */
    protected string $fieldexpression;

    /** @var string */
    protected string $eofChar;

    /** @var resource|closed-resource */
    protected $handle;

    /** @var array */
    protected array $current;

    /** @var bool|string */
    protected string|bool $currentBuffer = "";

    /**
     * @access public
     * @param resource|closed-resource $handle
     * @param array $fields
     * @param string $eofChar
     * @param string $fieldExpression
     */
    public function __construct($handle, array $fields, string $fieldExpression, string $eofChar)
    {
        $this->fields = $fields;
        $this->fieldexpression = $fieldExpression;
        $this->eofChar = $eofChar;
        $this->handle = $handle;

        $this->current = [
            'row' => null,
            'i' => 0,
        ];

        $this->readNextLine();
    }

    /**
     * @return RowArray|null
     */
    protected function readNextLine(): ?RowArray
    {
        if (!$this->valid()) {
            return null;
        }

        if (empty($this->eofChar)) {
            $buffer = fgets($this->handle, 8192);
        } else {
            $buffer = stream_get_line($this->handle, 8192, $this->eofChar);
        }
        
        $this->currentBuffer = false;

        if (($buffer !== false) && (trim($buffer) != "")) {
            $this->currentBuffer = $buffer;
        } else {
            $this->readNextLine();
        }

        $row = $this->parseLine();

        $this->current["row"] = $row;

        return $row;

    }

    /**
     * @return RowArray|null
     */
    public function parseLine(): ?RowArray
    {
        $cols = preg_split($this->fieldexpression, preg_replace("/(\r?\n?)$/", "", $this->currentBuffer), -1, PREG_SPLIT_DELIM_CAPTURE);

        $row = new RowArray();

        for ($i = 0; ($i < count($this->fields)) && ($i < count($cols)); $i++) {
            $column = $cols[$i];

            if (($i >= count($this->fields) - 1) || ($i >= count($cols) - 1)) {
                $column = preg_replace("/(\r?\n?)$/", "", $column);
            }
            $column = preg_replace("/^[\"'](.*)[\"']$/", "$1", $column);

            $row->set($this->fields[$i], $column);
        }

        return $row;
    }

    public function key(): int
    {
        return $this->current["i"];
    }

    #[ReturnTypeWillChange]
    public function current(): mixed
    {
        return $this->current["row"];
    }

    #[ReturnTypeWillChange]
    public function next(): void
    {
        $this->current["i"]++;
        $this->current["row"] = null;
        $this->readNextLine();
    }

    #[ReturnTypeWillChange]
    public function valid(): bool
    {
        if ($this->currentBuffer !== false) {
            return true;
        }

        if (!$this->handle) {
            return false;
        }

        if (feof($this->handle)) {
            fclose($this->handle);
            $this->handle = null;
            return false;
        }

        return true;
    }
}
