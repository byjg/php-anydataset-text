<?php

namespace Tests\AnyDataset\Dataset;

use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\IteratorInterface;
use ByJG\AnyDataset\Core\Row;
use ByJG\AnyDataset\Text\Formatter\CSVFormatter;
use ByJG\AnyDataset\Text\TextFileDataset;
use PHPUnit\Framework\TestCase;

class TextFileDatasetTest extends TestCase
{

    protected static $fieldNames;
    protected static $fileName_Unix = "";
    protected static $fileName_Windows = "";
    protected static $fileName_MacClassic = "";
    protected static $fileName_BlankLine = "";

    const REMOTEURL = "https://opensource-test-resources.web.app/%s";

    public static function setUpBeforeClass(): void
    {
        self::$fileName_Unix = sys_get_temp_dir() . "/textfiletest-unix.csv";
        self::$fileName_Windows = sys_get_temp_dir() . "/textfiletest-windows.csv";
        self::$fileName_MacClassic = sys_get_temp_dir() . "/textfiletest-mac.csv";
        self::$fileName_BlankLine = sys_get_temp_dir() . "/textfiletest-bl.csv";

        $text = "";
        for ($i = 1; $i <= 2000; $i++) {
            $text .= "$i;STRING$i;VALUE$i\n";
        }
        file_put_contents(self::$fileName_Unix, $text);

        $text = "";
        for ($i = 1; $i <= 2000; $i++) {
            $text .= "$i;STRING$i;VALUE$i\r\n";
        }
        file_put_contents(self::$fileName_Windows, $text);

        $text = "";
        for ($i = 1; $i <= 2000; $i++) {
            $text .= "$i;STRING$i;VALUE$i\r";
        }
        file_put_contents(self::$fileName_MacClassic, $text);

        $text = "";
        for ($i = 1; $i <= 2000; $i++) {
            if (rand(0, 10) < 3) {
                $text .= "\n";
            }
            $text .= "$i;STRING$i;VALUE$i\n";
        }
        file_put_contents(self::$fileName_BlankLine, $text);

        // A lot of extras fields
        self::$fieldNames = array();
        for ($i = 1; $i < 30; $i++) {
            self::$fieldNames[] = "Field$i";
        }
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::$fileName_Unix);
        unlink(self::$fileName_Windows);
        unlink(self::$fileName_MacClassic);
        unlink(self::$fileName_BlankLine);
    }

    public function testcreateTextFileData_Unix()
    {
        $txtFile = new TextFileDataset(self::$fileName_Unix, self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $this->assertTrue($txtIterator instanceof IteratorInterface, "Resultant object must be an interator");
        $this->assertTrue($txtIterator->hasNext(), "hasNext() method must be true");
        $this->assertTrue($txtIterator->Count() == -1, "Count() does not return anything by default.");
        $this->assertRowCount($txtIterator, 2000);
    }

    public function testcreateTextFileData_Windows()
    {
        $txtFile = new TextFileDataset(self::$fileName_Windows, self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $this->assertTrue($txtIterator instanceof IteratorInterface);
        $this->assertTrue($txtIterator->hasNext());
        $this->assertEquals($txtIterator->Count(), -1);
        $this->assertRowCount($txtIterator, 2000);
    }

    public function testcreateTextFileData_MacClassic()
    {
        $txtFile = new TextFileDataset(self::$fileName_MacClassic, self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $this->assertTrue($txtIterator instanceof IteratorInterface);
        $this->assertTrue($txtIterator->hasNext());
        $this->assertEquals($txtIterator->Count(), -1);
        $this->assertRowCount($txtIterator, 2000);
    }

    public function testcreateTextFileData_BlankLine()
    {
        $txtFile = new TextFileDataset(self::$fileName_BlankLine, self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $this->assertTrue($txtIterator instanceof IteratorInterface);
        $this->assertTrue($txtIterator->hasNext());
        $this->assertEquals($txtIterator->Count(), -1);
        $this->assertRowCount($txtIterator, 2000);
    }

    public function testnavigateTextIterator_Unix()
    {
        $txtFile = new TextFileDataset(self::$fileName_Windows, self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $count = 0;
        foreach ($txtIterator as $sr) {
            $this->assertSingleRow($sr, ++$count);
        }

        $this->assertEquals($count, 2000);
    }

    public function testnavigateTextIterator_Windows()
    {
        $txtFile = new TextFileDataset(self::$fileName_Windows, self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $count = 0;
        foreach ($txtIterator as $sr) {
            $this->assertSingleRow($sr, ++$count);
        }

        $this->assertEquals($count, 2000);
    }

    public function testnavigateTextIterator_MacClassic()
    {
        $txtFile = new TextFileDataset(self::$fileName_Windows, self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $count = 0;
        foreach ($txtIterator as $sr) {
            $this->assertSingleRow($sr, ++$count);
        }

        $this->assertEquals($count, 2000);
    }

    public function testnavigateTextIterator_BlankLine()
    {
        $txtFile = new TextFileDataset(self::$fileName_BlankLine, self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $count = 0;
        foreach ($txtIterator as $sr) {
            $this->assertSingleRow($sr, ++$count);
        }

        $this->assertEquals($count, 2000);
    }

    public function testnavigateTextIterator_Remote_Unix()
    {
        $txtFile = new TextFileDataset(
            sprintf(self::REMOTEURL, basename(self::$fileName_Unix)),
            self::$fieldNames,
            TextFileDataset::CSVFILE
        );
        $txtIterator = $txtFile->getIterator();

        $count = 0;
        foreach ($txtIterator as $sr) {
            $this->assertSingleRow($sr, ++$count);
        }

        $this->assertEquals($count, 2000);
    }

    public function testnavigateTextIterator_Remote_Windows()
    {
        $txtFile = new TextFileDataset(
            sprintf(self::REMOTEURL, basename(self::$fileName_Windows)),
            self::$fieldNames,
            TextFileDataset::CSVFILE
        );
        $txtIterator = $txtFile->getIterator();

        $count = 0;
        foreach ($txtIterator as $sr) {
            $this->assertSingleRow($sr, ++$count);
        }

        $this->assertEquals($count, 2000);
    }

    /**
     * fsockopen and fgets is buggy when read a Mac classic document (\r line ending)
     */
    public function testnavigateTextIterator_Remote_MacClassic()
    {
        $txtFile = new TextFileDataset(
            sprintf(self::REMOTEURL, basename(self::$fileName_MacClassic)),
            self::$fieldNames,
            TextFileDataset::CSVFILE
        );
        $txtIterator = $txtFile->getIterator();

        $count = 0;
        foreach ($txtIterator as $sr) {
            $this->assertSingleRow($sr, ++$count);
        }

        $this->assertEquals($count, 2000);
    }

    public function testnavigateTextIterator_Remote_BlankLine()
    {
        $txtFile = new TextFileDataset(
            sprintf(self::REMOTEURL, basename(self::$fileName_BlankLine)),
            self::$fieldNames,
            TextFileDataset::CSVFILE
        );
        $txtIterator = $txtFile->getIterator();

        $count = 0;
        foreach ($txtIterator as $sr) {
            $this->assertSingleRow($sr, ++$count);
        }

        $this->assertEquals($count, 2000);
    }

    public function testfileNotFound()
    {
        $this->expectException(\ByJG\AnyDataset\Core\Exception\NotFoundException::class);

        new TextFileDataset("/tmp/xyz", self::$fieldNames, TextFileDataset::CSVFILE);
    }

    public function testremoteFileNotFound()
    {
        $this->expectException(\ByJG\AnyDataset\Core\Exception\DatasetException::class);

        $txtFile = new TextFileDataset(self::REMOTEURL . "notfound-test", self::$fieldNames, TextFileDataset::CSVFILE);
        $txtFile->getIterator();
    }

    /**
     * @expectedException \ByJG\AnyDataset\Core\Exception\DatasetException
     */
    public function testserverNotFound()
    {
        $this->expectException(\ByJG\AnyDataset\Core\Exception\DatasetException::class);

        $txtFile = new TextFileDataset("http://notfound-test/alalal", self::$fieldNames, TextFileDataset::CSVFILE);
        $txtFile->getIterator();
    }

    public function testRowCSVFormatter()
    {
        $row = new Row([
            "field1" => "value1",
            "field2" => "0.345",
            "field3" => 0.874,
            "field4" => 10,
            "field5" => 'With"Quote',
            "field6" => "With'SingleQuote",
            "field7" => "value7",
            "field8" => "val,ue7",
            "field9" => "value9",
        ]);

        $formatter = new CSVFormatter($row);

        $this->assertEquals('value1,0.345,0.874,10,"With""Quote",With\'SingleQuote,value7,"val,ue7",value9' . "\n", $formatter->toText());

        $formatter->setApplyQuote(CSVFormatter::APPLY_QUOTE_ALL_STRINGS);
        $this->assertEquals('"value1",0.345,0.874,10,"With""Quote","With\'SingleQuote","value7","val,ue7","value9"' . "\n", $formatter->toText());

        $formatter->setApplyQuote(CSVFormatter::APPLY_QUOTE_ALWAYS);
        $this->assertEquals('"value1","0.345","0.874","10","With""Quote","With\'SingleQuote","value7","val,ue7","value9"' . "\n", $formatter->toText());
    }

    public function testAnyDatasetRowFormatter()
    {
        $anydataset = new TextFileDataset(self::$fileName_Unix, self::$fieldNames);
        $formatter = new CSVFormatter($anydataset->getIterator());

        $text = "field1,field2,field3\n";
        for ($i = 1; $i <= 2000; $i++) {
            $text .= "$i,STRING$i,VALUE$i\n";
        }
        $this->assertEquals($text, $formatter->toText());

        $text = '"field1","field2","field3"' . "\n";
        for ($i = 1; $i <= 2000; $i++) {
            $text .= "$i,\"STRING$i\",\"VALUE$i\"\n";
        }

        $anydataset = new TextFileDataset(self::$fileName_Unix, self::$fieldNames);
        $formatter = new CSVFormatter($anydataset->getIterator());
        $formatter->setApplyQuote(CSVFormatter::APPLY_QUOTE_ALL_STRINGS);
        $this->assertEquals($text, $formatter->toText());

        $text = '"field1","field2","field3"' . "\n";
        for ($i = 1; $i <= 2000; $i++) {
            $text .= "\"$i\",\"STRING$i\",\"VALUE$i\"\n";
        }

        $anydataset = new TextFileDataset(self::$fileName_Unix, self::$fieldNames);
        $formatter = new CSVFormatter($anydataset->getIterator());
        $formatter->setApplyQuote(CSVFormatter::APPLY_QUOTE_ALWAYS);
        $this->assertEquals($text, $formatter->toText());
    }

    /**

     * @param Row $sr
     */
    public function assertSingleRow($sr, $count)
    {
        $this->assertEquals($sr->get("field1"), $count);
        $this->assertEquals($sr->get("field2"), "STRING$count");
        $this->assertEquals($sr->get("field3"), "VALUE$count");
    }

    /**
     * @param GenericIterator $it
     * @param $qty
     */
    public function assertRowCount(GenericIterator $it, $qty)
    {
        $result = $it->toArray();

        $this->assertEquals($qty, count($result));
    }
}
