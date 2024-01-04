<?php

namespace ByJG\AnyDataset\Text;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Row;

class TextFileIterator extends GenericIterator
{

    /** @var array */
    protected $fields;

    /** @var string */
    protected $fieldexpression;

    /** @var string */
    protected $eofChar;

    /** @var resource */
    protected $handle;

    /** @var int */
    protected $current = 0;

    /** @var bool|string */
    protected $currentBuffer = "";

    /**
     * @access public
     * @param resource $handle
     * @param array $fields
     * @param string $eofChar
     * @param string $fieldexpression
     */
    public function __construct($handle, $fields, $fieldexpression, $eofChar)
    {
        $this->fields = $fields;
        $this->fieldexpression = $fieldexpression;
        $this->eofChar = $eofChar;
        $this->handle = $handle;

        $this->readNextLine();
    }

    /**
     * @return void
     */
    protected function readNextLine()
    {
        if (!$this->hasNext()) {
            return;
        }

        if (empty($this->eofChar)) {
            /**
             * @psalm-suppress PossiblyNullArgument
             */
            $buffer = fgets($this->handle, 8192);
        } else {
            /**
             * @psalm-suppress PossiblyNullArgument
             */
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
    public function count()
    {
        return -1;
    }

    /**
     * @access public
     * @return bool
     */
    public function hasNext()
    {
        if ($this->currentBuffer !== false) {
            return true;
        }

        /**
         * @psalm-suppress DocblockTypeContradiction
         */
        if (!$this->handle) {
            return false;
        }

        if (feof($this->handle)) {
            /**
             * @psalm-suppress InvalidPropertyAssignmentValue
             */
            fclose($this->handle);
            /**
             * @psalm-suppress PossiblyNullPropertyAssignmentValue
             */
            $this->handle = null;
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function moveNext()
    {
        if ($this->hasNext()) {
            /**
             * @psalm-suppress InvalidScalarArgument
             */
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

        /**
         * @psalm-suppress RedundantConditionGivenDocblockType
         */
        if ($this->handle) {
            /**
             * @psalm-suppress InvalidPropertyAssignmentValue
             */
            fclose($this->handle);
        }
        return null;
    }

    public function key()
    {
        return $this->current;
    }
}
