<?php

namespace ByJG\AnyDataset\Text\Formatter;

use ByJG\AnyDataset\Core\Formatter\BaseFormatter;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Row;

class CSVFormatter extends BaseFormatter
{
    const APPLY_QUOTE_ALWAYS = 0;
    const APPLY_QUOTE_WHEN_REQUIRED = 1;
    const APPLY_QUOTE_ALL_STRINGS = 2;
    const NEVER_APPLY_QUOTE = 3;

    /** @var string */
    protected $delimiter = ",";

    /** @var string */
    protected $quote = '"';

    /** @var int */
    protected $applyQuote = 1;

    /** @var boolean */
    protected $outputHeader = true;

    /**
     * @param GenericIterator|Row $anydataset
     * @param string $delimiter
     * @param string $quote
     * @param int $applyQuote
     */
    public function __construct($anydataset, $delimiter = ",", $quote = '"', $applyQuote = 1)
    {
        parent::__construct($anydataset);

        $this->delimiter = $delimiter;
        $this->quote = $quote;
        $this->applyQuote = $applyQuote;
    }

    /**
     * @param GenericIterator $iterator
     * @return string
     */
    protected function anydatasetRaw($iterator)
    {
        $lines = "";

        if (!$iterator->hasNext()) {
            return $lines;
        }

        if ($this->outputHeader) {
            $row = $iterator->moveNext();
            $lines .= $this->rowRaw(array_keys($row->toArray()));
            $lines .= $this->rowRaw($row->toArray());
        }

        foreach ($iterator as $row) {
            $lines .= $this->rowRaw($row->toArray());
        }

        return $lines;
    }

    /**
     * @param array $row
     * @return string
     */
    protected function rowRaw($row)
    {
        $line = "";
        foreach ($row as $value) {
            $value = str_replace($this->quote, $this->quote . $this->quote, $value);
            $quoteStr = 
                ($this->getApplyQuote() == self::APPLY_QUOTE_ALWAYS)
                || ($this->getApplyQuote() == self::APPLY_QUOTE_WHEN_REQUIRED && (!is_numeric($value) && (strpos($value, $this->quote) !== false || strpos($value, $this->delimiter) !== false)))
                || ($this->getApplyQuote() == self::APPLY_QUOTE_ALL_STRINGS && !is_numeric($value))
                ? $this->quote : "";
            $line .= (!empty($line) ? $this->delimiter : "") . $quoteStr . $value . $quoteStr;
        }
        return $line . "\n";
    }

    public function raw(): mixed
    {
        if ($this->object instanceof GenericIterator) {
            return $this->anydatasetRaw($this->object);
        }
        return $this->rowRaw($this->object->toArray());
    }


    public function toText(): string
    {
        return $this->raw();
    }

    /**
     * @param string $value
     * @return void
     */
    public function setDelimiter($value) 
    {
        $this->delimiter = $value;
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @param string $value
     * @return void
     */
    public function setQuote($value) 
    {
        $this->quote = $value;
    }

    /**
     * @return string
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * @param int $value
     * @return void
     */
    public function setApplyQuote($value) 
    {
        $this->applyQuote = $value;
    }

    /**
     * @return int
     */
    public function getApplyQuote()
    {
        return $this->applyQuote;
    }
    /**
     * @return bool
     */
    function getOutputHeader() {
        return $this->outputHeader;
    }
    
    /**
     * @param bool $outputHeader 
     * @return void
     */
    function setOutputHeader($outputHeader) {
        $this->outputHeader = $outputHeader;
    }
}