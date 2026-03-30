<?php

namespace HughCube\IdCard\Tests\Id;

use HughCube\IdCard\Contract\IdInterface;
use HughCube\IdCard\Id\TaiwanId;
use HughCube\IdCard\IdType;
use PHPUnit\Framework\TestCase;

class TaiwanIdTest extends TestCase
{
    public function testValidId()
    {
        $id = new TaiwanId('A123456789');
        $this->assertTrue($id->isValid());
        $this->assertSame('A123456789', $id->getCode());

        $id2 = new TaiwanId('a123456789');
        $this->assertTrue($id2->isValid());
    }

    public function testInvalidId()
    {
        $id = new TaiwanId('A123456781');
        $this->assertFalse($id->isValid());

        $id2 = new TaiwanId('1234567890');
        $this->assertFalse($id2->isValid());

        $id3 = new TaiwanId('A12345678');
        $this->assertFalse($id3->isValid());

        $id4 = new TaiwanId('A1234567890');
        $this->assertFalse($id4->isValid());
    }

    public function testGetGender()
    {
        $id = new TaiwanId('A123456789');
        $this->assertSame(1, $id->getGender());

        $id2 = new TaiwanId('A223456789');
        $this->assertSame(0, $id2->getGender());

        $id3 = new TaiwanId('A823456789');
        $this->assertSame(1, $id3->getGender());

        $id4 = new TaiwanId('A923456789');
        $this->assertSame(0, $id4->getGender());

        $id5 = new TaiwanId('A523456789');
        $this->assertNull($id5->getGender());
    }

    public function testGetRegionCode()
    {
        $id = new TaiwanId('A123456789');
        $this->assertSame('台北市', $id->getRegionCode());

        $id2 = new TaiwanId('B123456789');
        $this->assertSame('台中市', $id2->getRegionCode());

        $id3 = new TaiwanId('Z123456789');
        $this->assertSame('连江县', $id3->getRegionCode());
    }

    public function testComplete()
    {
        $results = iterator_to_array(TaiwanId::complete('A12345678*'));
        $this->assertCount(1, $results);
        $this->assertSame('A123456789', $results[0]);

        $id = new TaiwanId($results[0]);
        $this->assertTrue($id->isValid());
    }

    public function testCompleteInvalidPattern()
    {
        $results = iterator_to_array(TaiwanId::complete('123'));
        $this->assertCount(0, $results);
    }

    public function testInstanceOf()
    {
        $id = new TaiwanId('A123456789');
        $this->assertInstanceOf(IdInterface::class, $id);
    }

    public function test_get_type()
    {
        $id = new TaiwanId('A123456789');
        $this->assertSame(IdType::TAIWAN_ID, $id->getType());
    }

    public function test_mask()
    {
        $id = new TaiwanId('A123456789');
        $this->assertSame('A1****6789', $id->mask());

        $id2 = new TaiwanId('12345');
        $this->assertNull($id2->mask());
    }
}
