<?php

namespace ByJG\AnyDataset\Text;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Row;
use ByJG\AnyDataset\Text\Enum\FixedTextDefinition;
use ByJG\AnyDataset\Core\Exception\IteratorException;

class FixedTextFileIterator extends GenericIterator
{

    /**
     *
     * @var FixedTextDefinition[]
     */
    protected $fields;

    /**
     * @var resource
     */
    protected $handle;

    /**
     * @var int
     */
    protected $current = 0;

    /**
     *
     * @param resource $handle
     * @param FixedTextDefinition[] $fields
     */
    public function __construct($handle, $fields)
    {
        $this->fields = $fields;
        $this->handle = $handle;
        $this->current = 0;
    }

    /**
     * @access public
     * @return int
     */
    public function count()
    {
        return -1;
    }

    /**
     * @access public
     * @return bool
     */
    public function hasNext()
    {
        if (!$this->handle) {
            return false;
        }

        if (feof($this->handle)) {
            fclose($this->handle);

            return false;
        }

        return true;
    }


    /**
     * @return Row|null
     * @throws IteratorException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function moveNext()
    {
        if ($this->hasNext()) {
            $buffer = fgets($this->handle, 4096);

            if ($buffer == "") {
                return new Row();
            }

            $fields = $this->processBuffer($buffer, $this->fields);

            if (is_null($fields)) {
                throw new IteratorException("Definition does not match");
            }

            $this->current++;
            return new Row($fields);
        }

        if ($this->handle) {
            fclose($this->handle);
        }
        return null;
    }

    /**
     * @param $buffer
     * @param $fieldDefinition
     * @return array
     * @throws IteratorException
     */
    protected function processBuffer($buffer, $fieldDefinition)
    {
        $cntDef = count($fieldDefinition);
        $fields = [];
        for ($i = 0; $i < $cntDef; $i++) {
            $fieldDef = $fieldDefinition[$i];

            $fields[$fieldDef->fieldName] = trim(substr($buffer, $fieldDef->startPos, $fieldDef->length));
            if (!empty($fieldDef->requiredValue) && !in_array($fields[$fieldDef->fieldName], $fieldDef->requiredValue)) {
                throw new IteratorException(
                    "Expected the values '"
                    . implode(",", $fieldDef->requiredValue)
                    . "' and I got '"
                    . $fields[$fieldDef->fieldName]
                    . "'"
                );
            }

            if (empty($fieldDef->subTypes) && $fieldDef->type == FixedTextDefinition::TYPE_NUMBER) {
                $fields[$fieldDef->fieldName] = $fields[$fieldDef->fieldName] + 0; # Force convert to number
            }

            if (is_array($fieldDef->subTypes)) {
                if (!isset($fieldDef->subTypes[$fields[$fieldDef->fieldName]])) {
                    throw new IteratorException("Subtype does not match");
                }

                $value = $fieldDef->subTypes[$fields[$fieldDef->fieldName]];

                if (!is_array($value)) {
                    throw new \InvalidArgumentException("Subtype needs to be an array");
                }

                $fields = array_merge(
                    $fields,
                    $this->processBuffer($buffer, $value)
                );
            }
        }

        return $fields;
    }

    public function key()
    {
        return $this->current;
    }
}
