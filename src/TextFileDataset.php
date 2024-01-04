<?php

namespace ByJG\AnyDataset\Text;

use ByJG\AnyDataset\Core\Exception\DatasetException;
use ByJG\AnyDataset\Core\Exception\NotFoundException;
use ByJG\AnyDataset\Core\GenericIterator;
use Exception;
use InvalidArgumentException;

class TextFileDataset
{

    const CSVFILE = '/[|,;](?=(?:[^"]*"[^"]*")*(?![^"]*"))/';
    const CSVFILE_SEMICOLON = '/[;](?=(?:[^"]*"[^"]*")*(?![^"]*"))/';
    const CSVFILE_COMMA = '/[,](?=(?:[^"]*"[^"]*")*(?![^"]*"))/';

    /** @var string */
    protected $source;

    /** @var array */
    protected $fields;

    /** @var string */
    protected $fieldexpression;

    /** @var string */
    protected $sourceType;

    /** @var string */
    protected $eofChar = "";

    /**
     * Text File Data Set
     *
     * @param string $source
     * @param array $fields
     * @param string $fieldexpression
     * @throws NotFoundException
     */
    public function __construct($source)
    {
        $this->fieldexpression = TextFileDataset::CSVFILE;
        $this->fields = [];

        if (!preg_match('~(http|https|ftp)://~', $source)) {
            $this->source = $source;

            if (!file_exists($this->source)) {
                throw new NotFoundException("The specified file " . $this->source . " does not exists");
            }

            $this->sourceType = "FILE";
        } else {
            $this->source = $source;
            $this->sourceType = "HTTP";
        }
    }

    /**
     * @param string $source
     * @return TextFileDataset
     * @throws NotFoundException
     */
    public static function getInstance($source)
    {
        return new TextFileDataset($source);
    }

    /**
     * @param string $regexParser
     * @return TextFileDataset
     */
    public function withFieldParser($regexParser)
    {
        $this->fieldexpression = $regexParser;
        return $this;
    }

    /**
     * @param array $fields
     * @return TextFileDataset
     */
    public function withFields($fields)
    {
        /**
         * @psalm-suppress DocblockTypeContradiction
         */
        if (!is_array($fields)) {
            throw new InvalidArgumentException("You must define an array of fields.");
        }
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param string $char
     * @return TextFileDataset
     */
    public function withEofChar($char)
    {
        $this->eofChar = $char;
        return $this;
    }

    /**
     * @return GenericIterator
     * @throws DatasetException
     * @throws Exception
     */
    public function getIterator()
    {
        $handle = @fopen($this->source, "r");
        if (!$handle) {
            throw new DatasetException("TextFileDataset failed to open resource");
        }

        try {
            if (empty($this->fields)) {
                $this->fields = $this->getFieldDefinitionFromFile($handle);
            }
            return new TextFileIterator($handle, $this->fields, $this->fieldexpression, $this->eofChar);
        } catch (Exception $ex) {
            fclose($handle);
            throw $ex;
        }
    }

    /**
     * @param resource $handle
     * @return array
     */
    protected function getFieldDefinitionFromFile($handle)
    {
        $buffer = preg_replace("/(\r?\n?)$/", "", fgets($handle, 4096));
        $fieldList = preg_split($this->fieldexpression, $buffer, -1, PREG_SPLIT_DELIM_CAPTURE);
        /**
         * @psalm-suppress MissingClosureParamType
         */
        return array_map(function ($value) {
            return preg_replace("/^[\"'](.*)[\"']$/", "$1", $value);
        }, $fieldList);
    }
}
