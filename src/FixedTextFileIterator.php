<?php

namespace ByJG\AnyDataset\Text;

use ByJG\AnyDataset\Core\Exception\IteratorException;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\RowArray;
use ByJG\AnyDataset\Core\RowInterface;
use ByJG\AnyDataset\Text\Definition\FixedTextDefinition;
use ByJG\AnyDataset\Text\Definition\TextTypeEnum;
use ReturnTypeWillChange;

class FixedTextFileIterator extends GenericIterator
{

    /**
     *
     * @var FixedTextDefinition[]
     */
    protected array $fields;

    /**
     * @var resource|closed-resource
     */
    protected $handle;

    /**
     * @var array
     */
    private array $current;

    /**
     *
     * @param resource|closed-resource $handle
     * @param FixedTextDefinition[] $fieldDefinition
     */
    public function __construct($handle, array $fieldDefinition)
    {
        $this->fields = $fieldDefinition;
        $this->handle = $handle;
        $this->current = [
            'row' => null,
            'i' => 0,
        ];

        $this->readNextLine();
    }

    protected function readNextLine(): ?RowInterface
    {
        if (!$this->valid()) {
            return null;
        }

        $buffer = fgets($this->handle, 8192);

        if (!empty($buffer)) {
            $retFields = $this->processBuffer($buffer, $this->fields);
            $row = new RowArray($retFields);
        } else {
            $row = new RowArray();
        }

        $this->current["row"] = $row;

        return $row;
    }

    /**
     * @param string $buffer
     * @param FixedTextDefinition[] $fieldDefinition
     * @return array
     * @throws IteratorException
     */
    protected function processBuffer(string $buffer, array $fieldDefinition): array
    {
        $cntDef = count($fieldDefinition);
        $fieldList = [];
        for ($i = 0; $i < $cntDef; $i++) {
            $fieldDef = $fieldDefinition[$i];

            $fieldList[$fieldDef->fieldName] = trim(substr($buffer, $fieldDef->startPos, $fieldDef->length));
            if (!empty($fieldDef->requiredValue) && !in_array($fieldList[$fieldDef->fieldName], $fieldDef->requiredValue)) {
                throw new IteratorException(
                    "Expected the values '"
                    . implode(",", $fieldDef->requiredValue)
                    . "' and I got '"
                    . $fieldList[$fieldDef->fieldName]
                    . "'"
                );
            }

            if (empty($fieldDef->subTypes) && $fieldDef->type == TextTypeEnum::NUMBER) {
                /**
                 * This will convert the string to number. 
                 */
                $intVal = intval($fieldList[$fieldDef->fieldName]);
                $floatVal = floatval($fieldList[$fieldDef->fieldName]);
                $fieldList[$fieldDef->fieldName] = ($intVal == $floatVal) ? $intVal : $floatVal;
            }

            if (is_array($fieldDef->subTypes)) {
                if (!isset($fieldDef->subTypes[$fieldList[$fieldDef->fieldName]])) {
                    throw new IteratorException("Subtype does not match");
                }

                $value = $fieldDef->subTypes[$fieldList[$fieldDef->fieldName]];

                if (!is_array($value)) {
                    throw new \InvalidArgumentException("Subtype needs to be an array");
                }

                $fieldList = array_merge(
                    $fieldList,
                    $this->processBuffer($buffer, $value)
                );
            }
        }

        return $fieldList;
    }

    public function key(): int
    {
        return $this->current["i"];
    }

    #[ReturnTypeWillChange]
    public function current(): ?RowInterface
    {
        return $this->current["row"];
    }

    #[ReturnTypeWillChange]
    public function next(): void
    {
        $this->current["i"]++;
        $this->current["row"] = null;
        $this->readNextLine();
    }

    #[ReturnTypeWillChange]
    public function valid(): bool
    {
        if (!$this->handle) {
            return false;
        }

        if (feof($this->handle)) {
            fclose($this->handle);
            $this->handle = null;
            return true;
        }

        return true;
    }
}
