<?php

namespace ByJG\AnyDataset\Text\Formatter;

use ByJG\AnyDataset\Core\Formatter\BaseFormatter;
use ByJG\AnyDataset\Core\GenericIterator;

class CSVFormatter extends BaseFormatter
{
    const APPLY_QUOTE_ALWAYS = 0;
    const APPLY_QUOTE_WHEN_REQUIRED = 1;
    const APPLY_QUOTE_ALL_STRINGS = 2;
    const NEVER_APPLY_QUOTE = 3;

    protected $delimiter = ",";
    protected $quote = '"';
    protected $applyQuote = null;

    public function __construct($anydataset, $delimiter = ",", $quote = '"', $applyQuote = 1)
    {
        parent::__construct($anydataset);

        $this->$delimiter = $delimiter;
        $this->quote = $quote;
        $this->applyQuote = $applyQuote;
    }

    protected function anydatasetRaw($iterator)
    {
        $lines = "";
        $row = $iterator->moveNext();
        $lines .= $this->rowRaw(array_keys($row->toArray()));
        $lines .= $this->rowRaw($row->toArray());

        foreach ($iterator as $row) {
            $lines .= $this->rowRaw($row->toArray());
        }

        return $lines;
    }

    protected function rowRaw($row)
    {
        $line = "";
        foreach ($row as $key => $value) {
            $value = str_replace($this->quote, $this->quote . $this->quote, $value);
            $quote = 
                ($this->getApplyQuote() == self::APPLY_QUOTE_ALWAYS)
                || ($this->getApplyQuote() == self::APPLY_QUOTE_WHEN_REQUIRED && (!is_numeric($value) && (strpos($value, $this->quote) !== false || strpos($value, $this->delimiter) !== false)))
                || ($this->getApplyQuote() == self::APPLY_QUOTE_ALL_STRINGS && !is_numeric($value))
                ? $this->quote : "";
            $line .= (!empty($line) ? $this->delimiter : "") . $quote . $value . $quote;
        }
        return $line . "\n";
    }

    public function raw()
    {
        if ($this->object instanceof GenericIterator) {
            return $this->anydatasetRaw($this->object);
        }
        return $this->rowRaw($this->object->toArray());
    }


    public function toText()
    {
        return $this->raw();
    }

    public function setDelimiter($value) 
    {
        $this->delimiter = $value;
    }

    public function getDelimiter()
    {
        return $this->delimiter;
    }

    public function setQuote($value) 
    {
        $this->quote = $value;
    }

    public function getQuote()
    {
        return $this->quote;
    }

    public function setApplyQuote($value) 
    {
        $this->applyQuote = $value;
    }

    public function getApplyQuote()
    {
        return $this->applyQuote;
    }
}