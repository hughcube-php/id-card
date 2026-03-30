<?php

namespace HughCube\IdCard\Tests\Id;

use HughCube\IdCard\Contract\HongKongIssuedInterface;
use HughCube\IdCard\Contract\IdInterface;
use HughCube\IdCard\Id\HongKongId;
use HughCube\IdCard\IdType;
use PHPUnit\Framework\TestCase;

class HongKongIdTest extends TestCase
{
    public function testValidSingleLetter()
    {
        $id = new HongKongId('G123456(A)');
        $this->assertTrue($id->isValid());
        $this->assertSame('G123456(A)', $id->getCode());
    }

    public function testInvalidCheckDigit()
    {
        $id = new HongKongId('G123456(3)');
        $this->assertFalse($id->isValid());
    }

    public function testValidDoubleLetterWithBrackets()
    {
        $id = new HongKongId('AB123456(9)');
        $this->assertTrue($id->isValid());
        $this->assertSame('AB123456(9)', $id->getCode());
    }

    public function testValidDoubleLetterWithoutBrackets()
    {
        $id = new HongKongId('AB1234569');
        $this->assertTrue($id->isValid());
    }

    public function testValidSingleLetterWithoutBrackets()
    {
        $id = new HongKongId('G123456A');
        $this->assertTrue($id->isValid());
    }

    public function testValidWithBrackets()
    {
        $id = new HongKongId('G123456(A)');
        $this->assertTrue($id->isValid());
    }

    public function testCompleteLastWildcard()
    {
        $results = iterator_to_array(HongKongId::complete('G123456(*)'));
        $this->assertCount(1, $results);
        $this->assertSame('G123456(A)', $results[0]);
    }

    public function testCompleteLastWildcardWithoutBrackets()
    {
        $results = iterator_to_array(HongKongId::complete('G123456*'));
        $this->assertCount(1, $results);
        $this->assertSame('G123456(A)', $results[0]);
    }

    public function testCompleteDoubleLetterLastWildcard()
    {
        $results = iterator_to_array(HongKongId::complete('AB123456(*)'));
        $this->assertCount(1, $results);
        $this->assertSame('AB123456(9)', $results[0]);
    }

    public function testInstanceOf()
    {
        $id = new HongKongId('G123456(A)');
        $this->assertInstanceOf(IdInterface::class, $id);
        $this->assertInstanceOf(HongKongIssuedInterface::class, $id);
    }

    public function testInvalidFormat()
    {
        $id = new HongKongId('12345678');
        $this->assertFalse($id->isValid());

        $id = new HongKongId('');
        $this->assertFalse($id->isValid());

        $id = new HongKongId('ABC123456(7)');
        $this->assertFalse($id->isValid());
    }

    public function testCheckDigitZero()
    {
        $id = new HongKongId('K000000(0)');
        $this->assertTrue($id->isValid());
    }

    public function testKnownRealWorldData()
    {
        $this->assertTrue((new HongKongId('A123456(3)'))->isValid());
    }

    public function test_get_type()
    {
        $id = new HongKongId('G123456(A)');
        $this->assertSame(IdType::HONG_KONG_ID, $id->getType());
    }

    public function test_mask()
    {
        $id = new HongKongId('G123456(A)');
        $this->assertSame('G1****6(A)', $id->mask());

        $id2 = new HongKongId('AB123456(9)');
        $this->assertSame('AB1****6(9)', $id2->mask());

        $id3 = new HongKongId('invalid');
        $this->assertNull($id3->mask());
    }
}
