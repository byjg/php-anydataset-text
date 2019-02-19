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

        $repository = new FixedTextFileDataset(__DIR__ . '/sample-fixed.txt', $fieldDefinition);

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
     */
    public function testGetIteratorException()
    {
        $fieldDefinition = [
            new FixedTextDefinition('id', 0, 3),
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition('enable', 10, 1,'Y|N'),
            new FixedTextDefinition('code', 11, 4),
        ];

        $repository = new FixedTextFileDataset(__DIR__ . '/sample-fixed.txt', $fieldDefinition);
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
            new FixedTextDefinition('enable', 10, 1, 'S|N'),
            new FixedTextDefinition(
                'code',
                11,
                4,
                null,
                [
                    new FixedTextDefinition('first', 0, 1),
                    new FixedTextDefinition('second', 1, 3),
                ]
            ),
        ];

        $repository = new FixedTextFileDataset(__DIR__ . '/sample-fixed.txt', $fieldDefinition);

        $this->assertEquals([
            0 => [
                'id' => '001',
                'name' => 'JOAO   ',
                'enable' => 'S',
                'code' => [
                    'first' => '1',
                    'second' => '520'
                ]
            ],
            1 => [
                'id' => '002',
                'name' => 'GILBERT',
                'enable' => 'N',
                'code' => [
                    'first' => '1',
                    'second' => '621'
                ]
            ]
            ], $repository->getIterator()->toArray());
    }
}
