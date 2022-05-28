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

    protected $source;
    protected $fields;
    protected $fieldexpression;
    protected $sourceType;

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
        $this->fields = null;

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
     * @throws NotFoundException
     */
    public static function getInstance($source)
    {
        return new TextFileDataset($source);
    }

    public function withFieldParser($regexParser)
    {
        $this->fieldexpression = $regexParser;
        return $this;
    }

    public function withFields($fields)
    {
        if (!is_array($fields)) {
            throw new InvalidArgumentException("You must define an array of fields.");
        }
        $this->fields = $fields;
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
        $old = ini_set('auto_detect_line_endings', true);
        $handle = @fopen($this->source, "r");
        ini_set('auto_detect_line_endings', $old);
        if (!$handle) {
            throw new DatasetException("TextFileDataset failed to open resource");
        }

        try {
            if (empty($this->fields)) {
                $this->fields = $this->getFieldDefinitionFromFile($handle);
            }
            return new TextFileIterator($handle, $this->fields, $this->fieldexpression);
        } catch (Exception $ex) {
            fclose($handle);
            throw $ex;
        }
    }

    protected function getFieldDefinitionFromFile($handle)
    {
        $buffer = preg_replace("/(\r?\n?)$/", "", fgets($handle, 4096));
        $fields = preg_split($this->fieldexpression, $buffer, -1, PREG_SPLIT_DELIM_CAPTURE);
        return array_map(function ($value) {
            return preg_replace("/^[\"'](.*)[\"']$/", "$1", $value);
        }, $fields);
    }
}
