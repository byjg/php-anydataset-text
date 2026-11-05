<?php

namespace Tests;

use ByJG\AnyDataset\Core\Exception\DatasetException;
use ByJG\AnyDataset\Text\TextFileDataset;
use Override;
use PHPUnit\Framework\TestCase;

class TextFileDatasetCsvTest extends TestCase
{
    private string $filename;

    #[Override]
    protected function setUp(): void
    {
        $this->filename = sys_get_temp_dir() . '/textfile-csv-' . uniqid() . '.csv';
    }

    #[Override]
    protected function tearDown(): void
    {
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
    }

    private function dataset(string $content): TextFileDataset
    {
        file_put_contents($this->filename, $content);
        return TextFileDataset::getInstance($this->filename);
    }

    public function testRecordsKeyedByTheHeaderInLowerCase(): void
    {
        $dataset = $this->dataset(
            "ID,Name,Note\r\n"
            . "1,\"Doe, John\",\"He said \"\"hi\"\"\"\r\n"
            . "2,Jane,\"line 1\nline 2\"\r\n"
        )->withSeparator(',');

        $this->assertSame([
            ['id' => '1', 'name' => 'Doe, John', 'note' => 'He said "hi"'],
            ['id' => '2', 'name' => 'Jane', 'note' => "line 1\nline 2"],
        ], $dataset->getIterator()->toArray());
    }

    public function testSeparatorAndEnclosure(): void
    {
        $dataset = $this->dataset("id;name\n1;'Doe; John'\n2;\"Jane\"\n\n3;'O''Brien'\n")
            ->withSeparator(';', "'");

        // With ' as the enclosure, " is a plain character; the empty line is skipped
        $this->assertSame([
            ['id' => '1', 'name' => 'Doe; John'],
            ['id' => '2', 'name' => '"Jane"'],
            ['id' => '3', 'name' => "O'Brien"],
        ], $dataset->getIterator()->toArray());
    }

    public function testFieldsWithoutHeader(): void
    {
        $dataset = $this->dataset("1|Joao|extra\n2\n")
            ->withFields(['ID', 'Name'])
            ->withSeparator('|');

        // A record with fewer values leaves the fields out; the extra values are ignored
        $this->assertSame([
            ['id' => '1', 'name' => 'Joao'],
            ['id' => '2'],
        ], $dataset->getIterator()->toArray());
    }

    public function testEmptyFile(): void
    {
        $dataset = $this->dataset("")->withSeparator(',');

        $this->assertSame([], $dataset->getIterator()->toArray());
    }

    public function testEofCharIsNotSupported(): void
    {
        $dataset = $this->dataset("id\n1\n")->withSeparator(',')->withEofChar("\r");

        $this->expectException(DatasetException::class);
        $this->expectExceptionMessage('withEofChar() is not supported with withSeparator()');
        $dataset->getIterator();
    }

    public function testFieldParserReplacesTheSeparator(): void
    {
        $dataset = $this->dataset("id,note\n1,\"line 1\nline 2\"\n")
            ->withSeparator(',')
            ->withFieldParser(TextFileDataset::CSVFILE_COMMA);

        // The field parser splits each line: the quoted line break makes two rows
        $this->assertCount(2, $dataset->getIterator()->toArray());
    }

    public function testFileIsClosedWhenAPartlyReadIteratorIsDestroyed(): void
    {
        $dataset = $this->dataset("id\n1\n2\n")->withSeparator(',');
        $streams = count(get_resources('stream'));

        $iterator = $dataset->getIterator();
        $this->assertSame(['id' => '1'], $iterator->first());
        $this->assertCount($streams + 1, get_resources('stream'));

        unset($iterator);
        $this->assertCount($streams, get_resources('stream'));
    }
}
