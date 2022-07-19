<?php

namespace ByJG\AnyDataset\Text\Enum;

use InvalidArgumentException;

/**
 * @package xmlnuke
 */
class FixedTextDefinition
{
    const TYPE_STRING = "string";
    const TYPE_NUMBER = "number";

    /** @var string */
    public $fieldName;

    /** @var int */
    public $startPos;

    /** @var int */
    public $length;

    /** @var array|null */
    public $requiredValue;

    /** @var string */
    public $type;

    /** @var array|null */
    public $subTypes = array();

    /**
     *
     * @param string $fieldName
     * @param int $startPos
     * @param int $length
     * @param string $type
     * @param array|null $requiredValue
     * @param FixedTextDefinition[] $subTypes
     */
    public function __construct($fieldName, $startPos, $length, $type = "string", $requiredValue = null, $subTypes = null)
    {
        $this->fieldName = $fieldName;
        $this->startPos = $startPos;
        $this->length = $length;
        $this->type = $type;
        $this->requiredValue = $requiredValue;
        $this->subTypes = $subTypes;

        /**
         * @psalm-suppress DocblockTypeContradiction
         */
        if (!empty($this->requiredValue) && !is_array($this->requiredValue)) {
            throw new InvalidArgumentException("Required Value must be empty or an ARRAY of values");
        }

        if ($this->type != self::TYPE_NUMBER && $this->type != self::TYPE_STRING) {
            throw new InvalidArgumentException("Type must be '" . self::TYPE_STRING . "' or '" . self::TYPE_NUMBER . "'");
        }
    }
}
