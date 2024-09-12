<?php

namespace ByJG\AnyDataset\Text;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Row;
use ByJG\AnyDataset\Text\Definition\FixedTextDefinition;
use ByJG\AnyDataset\Core\Exception\IteratorException;
use ByJG\AnyDataset\Text\Definition\TextTypeEnum;

class FixedTextFileIterator extends GenericIterator
{

    /**
     *
     * @var FixedTextDefinition[]
     */
    protected array $fields;

    /**
     * @var resource
     */
    protected $handle;

    /**
     * @var int
     */
    protected int $current = 0;

    /**
     *
     * @param resource $handle
     * @param FixedTextDefinition[] $fieldDefinition
     */
    public function __construct($handle, array $fieldDefinition)
    {
        $this->fields = $fieldDefinition;
        $this->handle = $handle;
        $this->current = 0;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return -1;
    }

    /**
     * @inheritDoc
     */
    public function hasNext(): bool
    {
        /**
         * @psalm-suppress DocblockTypeContradiction
         */
        if (!$this->handle) {
            return false;
        }

        if (feof($this->handle)) {
            /**
             * @psalm-suppress InvalidPropertyAssignmentValue
             */
            fclose($this->handle);

            return false;
        }

        return true;
    }


    /**
     * @inheritDoc
     */
    public function moveNext(): ?Row
    {
        if ($this->hasNext()) {
            $buffer = fgets($this->handle, 8192);

            if ($buffer == "") {
                return new Row();
            }

            $retFields = $this->processBuffer($buffer, $this->fields);

            $this->current++;
            return new Row($retFields);
        }

        /**
         * @psalm-suppress RedundantConditionGivenDocblockType
         */
        if ($this->handle) {
            /**
             * @psalm-suppress InvalidPropertyAssignmentValue
             */
            fclose($this->handle);
        }
        return null;
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
                 * @psalm-suppress InvalidOperand
                 */
                $fieldList[$fieldDef->fieldName] = $fieldList[$fieldDef->fieldName] + 0;
            }

            if (is_array($fieldDef->subTypes)) {
                if (!isset($fieldDef->subTypes[$fieldList[$fieldDef->fieldName]])) {
                    throw new IteratorException("Subtype does not match");
                }

                /**
                 * @psalm-suppress PossiblyInvalidArrayOffset
                 */
                $value = $fieldDef->subTypes[$fieldList[$fieldDef->fieldName]];

                if (!is_array($value)) {
                    throw new \InvalidArgumentException("Subtype needs to be an array");
                }

                $fieldList = array_merge(
                    $fieldList,
                    /**
                     * @psalm-suppress PossiblyNullArgument
                     */
                    $this->processBuffer($buffer, $value)
                );
            }
        }

        return $fieldList;
    }

    public function key(): int
    {
        return $this->current;
    }
}
