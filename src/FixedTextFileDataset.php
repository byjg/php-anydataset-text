<?php

namespace ByJG\AnyDataset\Text;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Text\Enum\FixedTextDefinition;
use ByJG\AnyDataset\Core\Exception\DatasetException;
use ByJG\AnyDataset\Core\Exception\NotFoundException;
use Exception;
use InvalidArgumentException;

class FixedTextFileDataset
{

    /**
     * @var string
     */
    protected $source;

    /**
     * @var FixedTextDefinition[]
     * @psalm-suppress PossiblyNullPropertyAssignmentValue
     */
    protected $fieldDefinition = null;

    /**
     * @var string
     */
    protected $sourceType;

    /**
     * Text File Data Set
     *
     * @param string $source
     * @throws NotFoundException
     */
    protected function __construct($source)
    {
        $this->source = $source;
        $this->sourceType = "HTTP";

        if (!preg_match("~^https?://~", $source)) {
            if (!file_exists($this->source)) {
                throw new NotFoundException("The specified file " . $this->source . " does not exists");
            }

            $this->sourceType = "FILE";
        }
    }

    /**
     * Undocumented function
     *
     * @param string $source
     * @return FixedTextFileDataset
     */
    public static function getInstance($source)
    {
        return new FixedTextFileDataset($source);
    }

    /**
     * @param FixedTextDefinition[] $fieldDefinition
     * @return FixedTextFileDataset
     */
    public function withFieldDefinition($fieldDefinition)
    {
        /**
         * @psalm-suppress DocblockTypeContradiction
         */
        if (!is_array($fieldDefinition)) {
            throw new InvalidArgumentException("You must define an array of FixedTextDefinition class.");
        }

        $this->fieldDefinition = $fieldDefinition;

        return $this;
    }

    /**
     * @access public
     * @return GenericIterator
     * @throws DatasetException
     * @throws Exception
     */
    public function getIterator()
    {
        if (empty($this->fieldDefinition)) {
            throw new InvalidArgumentException("Field definition is empty");
        }

        if ($this->sourceType == "HTTP") {
            return $this->getIteratorHttp();
        }
        return $this->getIteratorFile();
    }

    /**
     * @return GenericIterator
     * @throws DatasetException
     */
    protected function getIteratorHttp()
    {
        // Expression Regular:
        // [1]: http or ftp
        // [2]: Server name
        // [3]: Full Path
        $pat = "/(http|ftp|https):\/\/([\w+|\.]+)/i";
        $urlParts = preg_split($pat, $this->source, -1, PREG_SPLIT_DELIM_CAPTURE);

        $handle = fsockopen($urlParts[2], 80, $errno, $errstr, 30);
        if (!$handle) {
            throw new DatasetException("TextFileDataset Socket error: $errstr ($errno)");
        }

        $out = "GET " . $urlParts[4] . " HTTP/1.1\r\n";
        $out .= "Host: " . $urlParts[2] . "\r\n";
        $out .= "Connection: Close\r\n\r\n";

        try {
            fwrite($handle, $out);
        } catch (Exception $ex) {
            fclose($handle);
            throw new DatasetException($ex->getMessage());
        }

        return new FixedTextFileIterator($handle, $this->fieldDefinition);
    }

    /**
     * @return GenericIterator
     * @throws DatasetException
     */
    protected function getIteratorFile()
    {
        $handle = fopen($this->source, "r");
        if (!$handle) {
            throw new DatasetException("TextFileDataset File open error");
        }

        return new FixedTextFileIterator($handle, $this->fieldDefinition);
    }
}
