<?php

namespace ByJG\AnyDataset\Text\Formatter;

use ByJG\AnyDataset\Core\Formatter\BaseFormatter;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Row;
use ByJG\AnyDataset\Text\Definition\FixedTextDefinition;
use ByJG\AnyDataset\Text\Definition\TextTypeEnum;
use ByJG\AnyDataset\Text\Exception\MalformedException;

class FixedSizeColumnFormatter extends BaseFormatter
{
    /**
     * @var FixedTextDefinition[]
     */
    protected array $fieldDefinition;
    
    /**
     * @var string
     */
    protected string $padNumber = '0';

    /**
     * @var string
     */
    protected string $padString = " ";

    /**
     * 
     *
     * @param GenericIterator|Row $anydataset
     * @param FixedTextDefinition[] $fieldDefinition
     */
    public function __construct(GenericIterator|Row $anydataset, array $fieldDefinition)
    {
        parent::__construct($anydataset);

        $this->fieldDefinition = $fieldDefinition;
    }

    /**
     * @param GenericIterator $iterator
     * @return string
     * @throws MalformedException
     */
    protected function anydatasetRaw(GenericIterator $iterator): string
    {
        $lines = "";
        foreach ($iterator as $row) {
            $lines .= $this->rowRaw($row->toArray());
        }

        return $lines;
    }

    /**
     * @param array $row
     * @param FixedTextDefinition|FixedTextDefinition[]|null $fieldDefinition
     * @param string $eof
     * @return string
     */
    protected function rowRaw(array $row, FixedTextDefinition|array $fieldDefinition = null, string $eof = "\n"): string
    {
        if (empty($fieldDefinition)) {
            $fieldDefinition = $this->fieldDefinition;
        } 
        if (!is_array($fieldDefinition)) {
            $fieldDefinition = [$fieldDefinition];
        }

        usort($fieldDefinition, function($a, $b) { return $a->startPos - $b->startPos; });
       
        $line = "";
        foreach ($fieldDefinition as $definition) {
            if (!isset($row[$definition->fieldName])) {
                throw new MalformedException("Field '$definition->fieldName' doesn't exist");
            }
            $value = $row[$definition->fieldName];

            if (strlen($value) > $definition->length) {
                throw new MalformedException("Field '$definition->fieldName' has maximum size of $definition->length but I got " . strlen($value) . " characters.");
            }
            if (!empty($definition->requiredValue) && (!in_array($value, $definition->requiredValue))) {
                throw new MalformedException("Field '$definition->fieldName' requires to be one of '" . implode(",", $definition->requiredValue) . "' but I found '$value'");
            }

            if ($definition->type == TextTypeEnum::NUMBER) {
                $line .= str_pad($value, $definition->length, $this->padNumber, STR_PAD_LEFT);
            } else {
                $line .= str_pad($value, $definition->length, $this->padString, STR_PAD_RIGHT);
            }

            if (!empty($definition->subTypes)) {
                $subTypes = null;
                if (!isset($definition->subTypes[$value])) {
                    throw new MalformedException("Sub type '$value' doesn't exist");
                }
                $subTypes = $definition->subTypes[$value];
                $line .= $this->rowRaw($row, $subTypes, "");
            }
        }
        return $line . $eof;
    }

    /**
     * @return string
     * @throws MalformedException
     */
    public function raw(): string
    {
        if ($this->object instanceof GenericIterator) {
            return $this->anydatasetRaw($this->object);
        }
        return $this->rowRaw($this->object->toArray());
    }


    /**
     * @throws MalformedException
     */
    public function toText(): string
    {
        return $this->raw();
    }


	/**
	 * 
	 * @return string
	 */
	function getPadNumber(): string
    {
		return $this->padNumber;
	}
	
	/**
	 * 
	 * @param string $padNumber 
     * @return void
	 */
	function setPadNumber(string $padNumber): void
    {
		$this->padNumber = $padNumber;
	}
	/**
	 * @return string
	 */
	function getPadString(): string
    {
		return $this->padString;
	}
	
	/**
	 * @param string $padString 
     * @return void
	 */
	function setPadString(string $padString): void
    {
		$this->padString = $padString;
	}
}