<?php

namespace Tests\AnyDataset\Dataset;

use ByJG\AnyDataset\Text\FixedTextFileDataset;
use ByJG\AnyDataset\Text\Enum\FixedTextDefinition;
use PHPUnit\Framework\TestCase;

class FixedTextFileDatasetTest extends TestCase
{

    /**
     * @var FixedTextFileDataset
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

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
     * @expectedException \ByJG\AnyDataset\Core\Exception\IteratorException
     * @expectedExceptionMessage Expected the value
     */
    public function testGetIteratorException()
    {
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
     * @expectedException \ByJG\AnyDataset\Core\Exception\IteratorException
     * @expectedExceptionMessage Subtype does not match
     */
    public function testGetIterator_SubTypes_Exception()
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Subtype needs to be an array
     */
    public function testGetIterator_SubTypes_Exception_Param()
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
                    "N" => new FixedTextDefinition('reason', 11, 4)
                ]
            ),
        ];

        $repository = FixedTextFileDataset::getInstance(__DIR__ . '/sample-fixed.txt')
            ->withFieldDefinition($fieldDefinition);
        $repository->getIterator()->toArray();
    }
}
