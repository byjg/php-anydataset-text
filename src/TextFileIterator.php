<?php

namespace ByJG\AnyDataset\Text;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Row;

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

    /** @var int */
    protected int $current = 0;

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

        $this->readNextLine();
    }

    /**
     * @return void
     */
    protected function readNextLine(): void
    {
        if (!$this->hasNext()) {
            return;
        }

        if (empty($this->eofChar)) {
            $buffer = fgets($this->handle, 8192);
        } else {
            $buffer = stream_get_line($this->handle, 8192, $this->eofChar);
        }
        
        $this->currentBuffer = false;

        if (($buffer !== false) && (trim($buffer) != "")) {
            $this->current++;
            $this->currentBuffer = $buffer;
        } else {
            $this->readNextLine();
        }
    }

    /**
     * @access public
     * @return int
     */
    public function count(): int
    {
        return -1;
    }

    /**
     * @access public
     * @return bool
     */
    public function hasNext(): bool
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

    /**
     * @inheritDoc
     * @return Row|null
     */
    public function moveNext(): ?Row
    {
        if ($this->hasNext()) {
            $cols = preg_split($this->fieldexpression, preg_replace("/(\r?\n?)$/", "", $this->currentBuffer), -1, PREG_SPLIT_DELIM_CAPTURE);

            $row = new Row();
            $row->enableFieldNameCaseInSensitive();

            // @todo review
            for ($i = 0; ($i < count($this->fields)) && ($i < count($cols)); $i++) {
                $column = $cols[$i];

                if (($i >= count($this->fields) - 1) || ($i >= count($cols) - 1)) {
                    $column = preg_replace("/(\r?\n?)$/", "", $column);
                }
                $column = preg_replace("/^[\"'](.*)[\"']$/", "$1", $column);

                $row->addField($this->fields[$i], $column);
            }

            $this->readNextLine();
            return $row;
        }

        if ($this->handle) {
            fclose($this->handle);
        }
        return null;
    }

    public function key(): int
    {
        return $this->current;
    }
}
