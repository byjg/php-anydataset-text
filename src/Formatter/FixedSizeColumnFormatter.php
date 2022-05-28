<?php

namespace ByJG\AnyDataset\Text\Formatter;

use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\AnyDataset\Core\Formatter\BaseFormatter;
use ByJG\AnyDataset\Text\Enum\FixedTextDefinition;
use ByJG\AnyDataset\Text\Exception\MalformedException;

class FixedSizeColumnFormatter extends BaseFormatter
{
    /**
     * @var FixedTextDefinition[]
     */
    protected $fieldDefinition = null;
    
    /**
     * @var string
     */
    protected $padNumber = '0';

    protected $padString = " ";

    public function __construct($anydataset, $fieldDefinition)
    {
        parent::__construct($anydataset);

        $this->fieldDefinition = $fieldDefinition;
    }

    protected function anydatasetRaw($iterator)
    {
        $lines = "";
        foreach ($iterator as $row) {
            $lines .= $this->rowRaw($row->toArray());
        }

        return $lines;
    }

    protected function rowRaw($row, $fieldDefinition = null, $eof = "\n")
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

            if ($definition->type == FixedTextDefinition::TYPE_NUMBER) {
                $line .= str_pad($value, $definition->length, $this->padNumber, STR_PAD_LEFT);
            } else {
                $line .= str_pad($value, $definition->length, $this->padString, STR_PAD_RIGHT);
            }

            if (!empty($definition->subTypes)) {
                $subTypes = $definition->subTypes;
                if (is_array($definition->subTypes)) {
                    if (!isset($definition->subTypes[$value])) {
                        throw new MalformedException("Sub type '$value' doesn't exist");
                    }
                    $subTypes = $definition->subTypes[$value];
                }
                $line .= $this->rowRaw($row, $subTypes, "");
            }
        }
        return $line . $eof;
    }

    public function raw()
    {
        if ($this->object instanceof AnyDataset) {
            return $this->anydatasetRaw($this->object->getIterator());
        }
        return $this->rowRaw($this->object->toArray());
    }


    public function toText()
    {
        return $this->raw();
    }


	/**
	 * 
	 * @return string
	 */
	function getPadNumber() {
		return $this->padNumber;
	}
	
	/**
	 * 
	 * @param string $padNumber 
	 */
	function setPadNumber($padNumber) {
		$this->padNumber = $padNumber;
	}
	/**
	 * @return string
	 */
	function getPadString() {
		return $this->padString;
	}
	
	/**
	 * @param string $padString 
	 */
	function setPadString($padString) {
		$this->padString = $padString;
	}
}