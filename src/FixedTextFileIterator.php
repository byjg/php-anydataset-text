<?php

namespace ByJG\AnyDataset\Text;

use ByJG\AnyDataset\Core\Exception\IteratorException;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\RowArray;
use ByJG\AnyDataset\Text\Definition\FixedTextDefinition;
use ByJG\AnyDataset\Text\Definition\TextTypeEnum;
use Generator;

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
     *
     * @param resource|closed-resource $handle
     * @param FixedTextDefinition[] $fieldDefinition
     */
    public function __construct($handle, array $fieldDefinition)
    {
        $this->fields = $fieldDefinition;
        $this->handle = $handle;
    }

    #[\Override]
    protected function rows(): Generator
    {
        return self::walk($this->handle, $this->fields);
    }

    /**
     * The rows of the file; the file is closed when they end or the iterator is destroyed
     *
     * @param resource|closed-resource $handle
     * @param FixedTextDefinition[] $fieldDefinition
     */
    private static function walk($handle, array $fieldDefinition): Generator
    {
        if (!is_resource($handle)) {
            return;
        }
        try {
            while (($buffer = fgets($handle, 8192)) !== false) {
                yield new RowArray(self::processBuffer($buffer, $fieldDefinition));
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param string $buffer
     * @param FixedTextDefinition[] $fieldDefinition
     * @return array
     * @throws IteratorException
     */
    protected static function processBuffer(string $buffer, array $fieldDefinition): array
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

            if (empty($fieldDef->subTypes) && $fieldDef->type == TextTypeEnum::Number) {
                /**
                 * This will convert the string to number. 
                 */
                $intVal = intval($fieldList[$fieldDef->fieldName]);
                $floatVal = floatval($fieldList[$fieldDef->fieldName]);
                $fieldList[$fieldDef->fieldName] = ($intVal == $floatVal) ? $intVal : $floatVal;
            }

            if (is_array($fieldDef->subTypes)) {
                $key = (string)$fieldList[$fieldDef->fieldName];
                if (!isset($fieldDef->subTypes[$key])) {
                    throw new IteratorException("Subtype does not match");
                }

                $value = $fieldDef->subTypes[$key];

                if (!is_array($value)) {
                    throw new \InvalidArgumentException("Subtype needs to be an array");
                }

                $fieldList = array_merge(
                    $fieldList,
                    self::processBuffer($buffer, $value)
                );
            }
        }

        return $fieldList;
    }
}
