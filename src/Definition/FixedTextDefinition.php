<?php

namespace ByJG\AnyDataset\Text\Definition;

use InvalidArgumentException;

/**
 * @package xmlnuke
 */
class FixedTextDefinition
{
    /** @var string */
    public string $fieldName;

    /** @var int */
    public int $startPos;

    /** @var int */
    public int $length;

    /** @var ?array */
    public ?array $requiredValue;

    /** @var TextTypeEnum */
    public TextTypeEnum $type;

    /** @var array|null */
    public ?array $subTypes = [];

    /**
     *
     * @param string $fieldName
     * @param int $startPos
     * @param int $length
     * @param TextTypeEnum $type
     * @param ?array $requiredValue
     * @param array|null $subTypes
     */
    public function __construct(string $fieldName, int $startPos, int $length, TextTypeEnum $type = TextTypeEnum::STRING, ?array $requiredValue = null, array $subTypes = null)
    {
        $this->fieldName = $fieldName;
        $this->startPos = $startPos;
        $this->length = $length;
        $this->type = $type;
        $this->requiredValue = $requiredValue;
        $this->subTypes = $subTypes;

        if ($this->type != TextTypeEnum::NUMBER && $this->type != TextTypeEnum::STRING) {
            throw new InvalidArgumentException("Type must be 'string' or 'number'");
        }
    }
}
