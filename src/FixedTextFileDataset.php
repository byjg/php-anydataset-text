<?php

namespace ByJG\AnyDataset\Text;

use ByJG\AnyDataset\Core\Exception\DatasetException;
use ByJG\AnyDataset\Core\Exception\NotFoundException;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Text\Definition\FixedTextDefinition;
use Exception;
use InvalidArgumentException;

class FixedTextFileDataset
{

    /**
     * @var string
     */
    protected string $source;

    /**
     * @var FixedTextDefinition[]
     */
    protected ?array $fieldDefinition = null;

    /**
     * @var string
     */
    protected string $sourceType;

    /**
     * Text File Data Set
     *
     * @param string $source
     * @throws NotFoundException
     */
    protected function __construct(string $source)
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
     * @throws NotFoundException
     */
    public static function getInstance(string $source): FixedTextFileDataset
    {
        return new FixedTextFileDataset($source);
    }

    /**
     * @param FixedTextDefinition[] $fieldDefinition
     * @return static
     */
    public function withFieldDefinition(array $fieldDefinition): static
    {
        $this->fieldDefinition = $fieldDefinition;

        return $this;
    }

    /**
     * @access public
     * @return GenericIterator
     * @throws DatasetException
     * @throws Exception
     */
    public function getIterator(): GenericIterator
    {
        if (empty($this->fieldDefinition)) {
            throw new InvalidArgumentException("Field definition is empty");
        }

        return $this->openStreamIterator();
    }

    /**
     * @return GenericIterator
     * @throws DatasetException
     */
    protected function openStreamIterator(): GenericIterator
    {
        // Local files and http(s) URLs are both read through PHP's stream wrappers
        $handle = @fopen($this->source, "r");
        if (!$handle) {
            throw new DatasetException("TextFileDataset File open error");
        }

        if ($this->fieldDefinition === null) {
            throw new InvalidArgumentException("Field definition is empty");
        }

        return new FixedTextFileIterator($handle, $this->fieldDefinition);
    }
}
