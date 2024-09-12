<?php

namespace ByJG\AnyDataset\Text;

use ByJG\AnyDataset\Core\Exception\DatasetException;
use ByJG\AnyDataset\Core\Exception\NotFoundException;
use ByJG\AnyDataset\Core\GenericIterator;
use Exception;

class TextFileDataset
{

    const CSVFILE = '/[|,;](?=(?:[^"]*"[^"]*")*(?![^"]*"))/';
    const CSVFILE_SEMICOLON = '/[;](?=(?:[^"]*"[^"]*")*(?![^"]*"))/';
    const CSVFILE_COMMA = '/[,](?=(?:[^"]*"[^"]*")*(?![^"]*"))/';

    /** @var string */
    protected string $source;

    /** @var array */
    protected array $fields;

    /** @var string */
    protected string $fieldexpression;

    /** @var string */
    protected string $sourceType;

    /** @var string */
    protected string $eofChar = "";

    /**
     * Text File Data Set
     *
     * @param string $source
     * @throws NotFoundException
     */
    public function __construct(string $source)
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
    public static function getInstance(string $source): TextFileDataset
    {
        return new TextFileDataset($source);
    }

    /**
     * @param string $regexParser
     * @return static
     */
    public function withFieldParser(string $regexParser): static
    {
        $this->fieldexpression = $regexParser;
        return $this;
    }

    /**
     * @param array $fields
     * @return static
     */
    public function withFields(array $fields): static
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param string $char
     * @return static
     */
    public function withEofChar(string $char): static
    {
        $this->eofChar = $char;
        return $this;
    }

    /**
     * @return GenericIterator
     * @throws DatasetException
     * @throws Exception
     */
    public function getIterator(): GenericIterator
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
    protected function getFieldDefinitionFromFile($handle): array
    {
        $buffer = preg_replace("/(\r?\n?)$/", "", fgets($handle, 4096));
        $fieldList = preg_split($this->fieldexpression, $buffer, -1, PREG_SPLIT_DELIM_CAPTURE);
        return array_map(function ($value) {
            return preg_replace("/^[\"'](.*)[\"']$/", "$1", $value);
        }, $fieldList);
    }
}
