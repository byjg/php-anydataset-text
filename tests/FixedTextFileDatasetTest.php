<?php

namespace Tests\AnyDataset\Dataset;

use ByJG\AnyDataset\Text\FixedTextFileDataset;
use ByJG\AnyDataset\Text\Enum\FixedTextDefinition;
use ByJG\AnyDataset\Core\Exception\IteratorException;
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
            new FixedTextDefinition('id', 0, 3),
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition('enable', 10, 1, 'S|N'),
            new FixedTextDefinition('code', 11, 4),
        ];

        $repository = FixedTextFileDataset::getInstance(__DIR__ . '/sample-fixed.txt')
            ->withFieldDefinition($fieldDefinition);

        $this->assertEquals([
            0 => [
                'id' => '001',
                'name' => 'JOAO   ',
                'enable' => 'S',
                'code' => '1520'
            ],
            1 => [
                'id' => '002',
                'name' => 'GILBERT',
                'enable' => 'N',
                'code' => '1621'
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
            new FixedTextDefinition('enable', 10, 1,'Y|N'),
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
            new FixedTextDefinition('id', 0, 3),
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition(
                'enable',
                10,
                1,
                null,
                [
                    "S" => [
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

        $this->assertEquals([
            0 => [
                'id' => '001',
                'name' => 'JOAO   ',
                'enable' => 'S',
                'first' => '1',
                'second' => '520'
            ],
            1 => [
                'id' => '002',
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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Subtype needs to be an array");

        $fieldDefinition = [
            new FixedTextDefinition('id', 0, 3),
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition(
                'enable',
                10,
                1,
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
}
