<?php

namespace ByJG\AnyDataset\Text;

use ByJG\AnyDataset\Core\Exception\DatasetException;
use ByJG\AnyDataset\Core\Exception\NotFoundException;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\Serializer\Reader\CsvReader;
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

    /** @var non-empty-string */
    protected string $fieldexpression;

    /** @var string */
    protected string $sourceType;

    /** @var string */
    protected string $eofChar = "";

    /** @var string|null The separator of the CSV records (withSeparator()); null splits the lines by the field parser */
    protected ?string $separator = null;

    /** @var string */
    protected string $enclosure = '"';

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
     * @param non-empty-string $regexParser
     * @return static
     */
    public function withFieldParser(string $regexParser): static
    {
        $this->fieldexpression = $regexParser;
        $this->separator = null;
        return $this;
    }

    /**
     * Reads the file as CSV records (RFC 4180) instead of splitting each line by the field parser: a value in the
     * enclosure may contain the separator, line breaks and the enclosure doubled. The lines end in LF or CRLF;
     * withEofChar() is not supported.
     *
     * @param string $separator The character between the fields (one byte)
     * @param string $enclosure The character that quotes a field (one byte)
     * @return static
     */
    public function withSeparator(string $separator, string $enclosure = '"'): static
    {
        $this->separator = $separator;
        $this->enclosure = $enclosure;
        return $this;
    }

    /**
     * @param array $fields
     * @return static
     */
    public function withFields(array $fields): static
    {
        $this->fields = array_map('strtolower', $fields);
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
        if ($this->separator !== null && $this->eofChar !== "") {
            throw new DatasetException("withEofChar() is not supported with withSeparator(): the CSV lines end in LF or CRLF");
        }

        $handle = @fopen($this->source, "r");
        if (!$handle) {
            throw new DatasetException("TextFileDataset failed to open resource");
        }

        try {
            if ($this->separator !== null) {
                if (empty($this->fields)) {
                    $header = CsvReader::records($handle, false, $this->separator, $this->enclosure)->current() ?? [];
                    $this->fields = array_map('strtolower', $header);
                }
                return new CsvFileIterator($handle, $this->fields, $this->separator, $this->enclosure);
            }

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
        $line = fgets($handle, 4096);
        if ($line === false) {
            return [];
        }

        $buffer = preg_replace("/(\r?\n?)$/", "", $line);
        if ($buffer === null) {
            return [];
        }

        $fieldList = preg_split($this->fieldexpression, $buffer, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($fieldList === false) {
            return [];
        }

        return array_map(function ($value) {
            $cleaned = preg_replace("/^[\"'](.*)[\"']$/", "$1", $value);
            return strtolower($cleaned ?? $value);
        }, $fieldList);
    }
}
