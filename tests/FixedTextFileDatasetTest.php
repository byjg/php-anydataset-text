<?php

namespace Tests\AnyDataset\Dataset;

use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\AnyDataset\Core\Exception\IteratorException;
use ByJG\AnyDataset\Core\Row;
use ByJG\AnyDataset\Text\FixedTextFileDataset;
use ByJG\AnyDataset\Text\Enum\FixedTextDefinition;
use ByJG\AnyDataset\Text\Exception\MalformedException;
use ByJG\AnyDataset\Text\Formatter\FixedSizeColumnFormatter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FixedTextFileDatasetTest extends TestCase
{

    /**
     * @var FixedTextFileDataset
     */
    protected $object;

    public function testGetIterator()
    {
        $fieldDefinition = [
            new FixedTextDefinition('id', 0, 3, FixedTextDefinition::TYPE_NUMBER),
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition('enable', 10, 1, FixedTextDefinition::TYPE_STRING, ['S', 'N']),
            new FixedTextDefinition('code', 11, 4, FixedTextDefinition::TYPE_NUMBER),
        ];

        $repository = FixedTextFileDataset::getInstance(__DIR__ . '/sample-fixed.txt')
            ->withFieldDefinition($fieldDefinition);

        $this->assertSame([
            0 => [
                'id' => 1,
                'name' => 'JOAO',
                'enable' => 'S',
                'code' => 1520
            ],
            1 => [
                'id' => 2,
                'name' => 'GILBERT',
                'enable' => 'N',
                'code' => 1621
            ]
            ], $repository->getIterator()->toArray());
    }

    /**
     * @throws \ByJG\AnyDataset\Core\Exception\DatasetException
     * @throws \ByJG\AnyDataset\Core\Exception\NotFoundException
     */
    public function testGetIteratorException()
    {
        $this->expectException(IteratorException::class);
        $this->expectExceptionMessage("Expected the value");

        $fieldDefinition = [
            new FixedTextDefinition('id', 0, 3),
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition('enable', 10, 1, FixedTextDefinition::TYPE_STRING, ['Y', 'N']),
            new FixedTextDefinition('code', 11, 4),
        ];

        $repository = FixedTextFileDataset::getInstance(__DIR__ . '/sample-fixed.txt')
            ->withFieldDefinition($fieldDefinition);
        $repository->getIterator()->toArray();
    }

    /**
     * @throws \ByJG\AnyDataset\Core\Exception\DatasetException
     * @throws \ByJG\AnyDataset\Core\Exception\NotFoundException
     */
    public function testGetIterator_SubTypes()
    {
        $fieldDefinition = [
            new FixedTextDefinition('id', 0, 3, FixedTextDefinition::TYPE_NUMBER),
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition(
                'enable',
                10,
                1,
                FixedTextDefinition::TYPE_STRING,
                null,
                [
                    "S" => [
                        new FixedTextDefinition('first', 11, 1, FixedTextDefinition::TYPE_NUMBER),
                        new FixedTextDefinition('second', 12, 3, FixedTextDefinition::TYPE_NUMBER),
                    ],
                    "N" => [
                        new FixedTextDefinition('reason', 11, 4),
                    ]
                ]
            ),
        ];

        $repository = FixedTextFileDataset::getInstance(__DIR__ . '/sample-fixed.txt')
            ->withFieldDefinition($fieldDefinition);

        $this->assertSame([
            0 => [
                'id' => 1,
                'name' => 'JOAO',
                'enable' => 'S',
                'first' => 1,
                'second' => 520
            ],
            1 => [
                'id' => 2,
                'name' => 'GILBERT',
                'enable' => 'N',
                'reason' => '1621'
            ]
            ], $repository->getIterator()->toArray());
    }

    /**
     * @throws \ByJG\AnyDataset\Core\Exception\DatasetException
     * @throws \ByJG\AnyDataset\Core\Exception\NotFoundException
     */
    public function testGetIterator_SubTypes_Exception()
    {
        $this->expectException(IteratorException::class);
        $this->expectExceptionMessage("Subtype does not match");

        $fieldDefinition = [
            new FixedTextDefinition('id', 0, 3),
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition(
                'enable',
                10,
                1,
                FixedTextDefinition::TYPE_STRING,
                null,
                [
                    "Y" => [
                        new FixedTextDefinition('first', 11, 1),
                        new FixedTextDefinition('second', 12, 3),
                    ],
                    "N" => [
                        new FixedTextDefinition('reason', 11, 4),
                    ]
                ]
            ),
        ];

        $repository = FixedTextFileDataset::getInstance(__DIR__ . '/sample-fixed.txt')
            ->withFieldDefinition($fieldDefinition);
        $repository->getIterator()->toArray();
    }

    /**
     * @throws \ByJG\AnyDataset\Core\Exception\DatasetException
     * @throws \ByJG\AnyDataset\Core\Exception\NotFoundException
     */
    public function testGetIterator_SubTypes_Exception_Param()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Subtype needs to be an array");

        $fieldDefinition = [
            new FixedTextDefinition('id', 0, 3),
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition(
                'enable',
                10,
                1,
                FixedTextDefinition::TYPE_STRING,
                null,
                [
                    "S" => [
                        new FixedTextDefinition('first', 11, 1),
                        new FixedTextDefinition('second', 12, 3),
                    ],
                    "N" => new FixedTextDefinition('reason', 11, 4)
                ]
            ),
        ];

        $repository = FixedTextFileDataset::getInstance(__DIR__ . '/sample-fixed.txt')
            ->withFieldDefinition($fieldDefinition);
        $repository->getIterator()->toArray();
    }

    public function testRowFormatter_1()
    {
        $fieldDefinition = [
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition('id', 0, 3, FixedTextDefinition::TYPE_NUMBER),
            new FixedTextDefinition('enable', 10, 1, FixedTextDefinition::TYPE_STRING, ['S', 'N']),
            new FixedTextDefinition('code', 11, 4),
        ];

        $row = new Row([
            "id" => 1,
            "name" => "Joao",
            "enable" => "N",
            "code" => 1520
        ]);
        $formatter = new FixedSizeColumnFormatter($row, $fieldDefinition);
        $this->assertEquals("001Joao   N1520\n", $formatter->toText());
    }

    public function testRowFormatter_1_Error_1()
    {
        $this->expectException(MalformedException::class);
        $this->expectExceptionMessage("Field 'enable' requires to be one of 'S,N' but I found 'X'");

        $fieldDefinition = [
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition('id', 0, 3),
            new FixedTextDefinition('enable', 10, 1, FixedTextDefinition::TYPE_STRING, ['S', 'N']),
            new FixedTextDefinition('code', 11, 4),
        ];

        $row = new Row([
            "id" => 1,
            "name" => "Joao",
            "enable" => "X",
            "code" => 1520
        ]);
        $formatter = new FixedSizeColumnFormatter($row, $fieldDefinition);
        $formatter->toText();
    }

    public function testRowFormatter_1_Error_2()
    {
        $this->expectException(MalformedException::class);
        $this->expectExceptionMessage("Field 'name' has maximum size of 7 but I got 15 characters.");

        $fieldDefinition = [
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition('id', 0, 3),
            new FixedTextDefinition('enable', 10, 1, FixedTextDefinition::TYPE_STRING, ['S', 'N']),
            new FixedTextDefinition('code', 11, 4),
        ];

        $row = new Row([
            "id" => 1,
            "name" => "Name Big Enough",
            "enable" => "S",
            "code" => 1520
        ]);
        $formatter = new FixedSizeColumnFormatter($row, $fieldDefinition);
        $formatter->toText();
    }

    public function testRowFormatter_1_Error_3()
    {
        $this->expectException(MalformedException::class);
        $this->expectExceptionMessage("Field 'id' doesn't exist");

        $fieldDefinition = [
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition('id', 0, 3),
            new FixedTextDefinition('enable', 10, 1, FixedTextDefinition::TYPE_STRING, ['S', 'N']),
            new FixedTextDefinition('code', 11, 4),
        ];

        $row = new Row([
            "name" => "Name Big Enough",
            "enable" => "S",
            "code" => 1520
        ]);
        $formatter = new FixedSizeColumnFormatter($row, $fieldDefinition);
        $this->assertEquals("001Joao   N1520\n", $formatter->toText());
    }

    public function testRowFormatter_2()
    {
        $fieldDefinition = [
            new FixedTextDefinition('id', 0, 3, FixedTextDefinition::TYPE_NUMBER),
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition(
                'enable',
                10,
                1,
                FixedTextDefinition::TYPE_STRING,
                null,
                [
                    "S" => [
                        new FixedTextDefinition('first', 11, 1),
                        new FixedTextDefinition('second', 12, 3),
                    ],
                    "N" => new FixedTextDefinition('reason', 11, 4)
                ]
            ),
        ];
    
        $row = new Row([
            "id" => 1,
            "name" => "Joao",
            "enable" => "N",
            "reason" => "NONE",
        ]);
        $formatter = new FixedSizeColumnFormatter($row, $fieldDefinition);
        $this->assertEquals("001Joao   NNONE\n", $formatter->toText());

        $row = new Row([
            "id" => 1,
            "name" => "Joao",
            "enable" => "S",
            "first" => "1",
            "second" => 520
        ]);
        $formatter = new FixedSizeColumnFormatter($row, $fieldDefinition);
        $this->assertEquals("001Joao   S1520\n", $formatter->toText());
    }

    public function testRowFormatter_2_Error_1()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Type must be 'string' or 'number'");

        $fieldDefinition = [
            new FixedTextDefinition('id', 0, 3),
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition(
                'enable',
                10,
                1,
                null,   # <--- Error
                null,
                [
                    "S" => [
                        new FixedTextDefinition('first', 11, 1),
                        new FixedTextDefinition('second', 12, 3),
                    ],
                    "N" => new FixedTextDefinition('reason', 11, 4)
                ]
            ),
        ];
    
        $row = new Row([
            "id" => 1,
            "name" => "Joao",
            "enable" => "X",
            "reason" => "NONE",
        ]);
        $formatter = new FixedSizeColumnFormatter($row, $fieldDefinition);
        $formatter->toText();
    }

    public function testRowFormatter_2_Error_2()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Required Value must be empty or an ARRAY of values");

        $fieldDefinition = [
            new FixedTextDefinition('id', 0, 3),
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition(
                'enable',
                10,
                1,
                FixedTextDefinition::TYPE_STRING,
                "S",    # <--- Error
                [
                    "S" => [
                        new FixedTextDefinition('first', 11, 1),
                        new FixedTextDefinition('second', 12, 3),
                    ],
                    "N" => new FixedTextDefinition('reason', 11, 4)
                ]
            ),
        ];
    
        $row = new Row([
            "id" => 1,
            "name" => "Joao",
            "enable" => "X",
            "reason" => "NONE",
        ]);
        $formatter = new FixedSizeColumnFormatter($row, $fieldDefinition);
        $formatter->toText();
    }

}
