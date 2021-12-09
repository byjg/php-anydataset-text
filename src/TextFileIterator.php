<?php

namespace ByJG\AnyDataset\Text;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Row;

class TextFileIterator extends GenericIterator
{

    protected $fields;
    protected $fieldexpression;
    protected $handle;
    protected $current = 0;
    protected $currentBuffer = "";

    /**
     * @access public
     * @param resource $handle
     * @param array $fields
     * @param string $fieldexpression
     */
    public function __construct($handle, $fields, $fieldexpression)
    {
        $this->fields = $fields;
        $this->fieldexpression = $fieldexpression;
        $this->handle = $handle;

        $this->readNextLine();
    }

    protected function readNextLine()
    {
        if (!$this->hasNext()) {
            return;
        }

        $buffer = fgets($this->handle, 4096);
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
     * @access public
     * @return Row
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function moveNext()
    {
        if ($this->hasNext()) {
            $cols = preg_split($this->fieldexpression, preg_replace("/(\r?\n?)$/", "", $this->currentBuffer), -1, PREG_SPLIT_DELIM_CAPTURE);

            $row = new Row();

            for ($i = 0; ($i < count($this->fields)) && ($i < count($cols)); $i++) {
                $column = preg_replace("/^[\"'](.*)[\"']$/", "$1", $cols[$i]);
                $row->addField(strtolower($this->fields[$i]), $column);
            }

            $this->readNextLine();
            return $row;
        }

        if ($this->handle) {
            fclose($this->handle);
        }
        return null;
    }

    public function key()
    {
        return $this->current;
    }
}
