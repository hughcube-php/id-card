<?php

namespace HughCube\IdCard\Tests\Id;

use HughCube\IdCard\Contract\IdInterface;
use HughCube\IdCard\Contract\MainlandIssuedInterface;
use HughCube\IdCard\Id\MainlandToHkMoPermit;
use HughCube\IdCard\Id\MainlandToTwPermit;
use HughCube\IdCard\IdType;
use PHPUnit\Framework\TestCase;

class MainlandPermitTest extends TestCase
{
    public function testHkMoValidC()
    {
        $permit = new MainlandToHkMoPermit('C12345678');
        $this->assertTrue($permit->isValid());
        $this->assertSame('C12345678', $permit->getCode());
    }

    public function testHkMoValidW()
    {
        $permit = new MainlandToHkMoPermit('W12345678');
        $this->assertTrue($permit->isValid());
        $this->assertSame('W12345678', $permit->getCode());
    }

    public function testHkMoValidLowerCase()
    {
        $permit = new MainlandToHkMoPermit('c12345678');
        $this->assertTrue($permit->isValid());

        $permit = new MainlandToHkMoPermit('w12345678');
        $this->assertTrue($permit->isValid());
    }

    public function testHkMoInvalidLetter()
    {
        $permit = new MainlandToHkMoPermit('H12345678');
        $this->assertFalse($permit->isValid());

        $permit = new MainlandToHkMoPermit('L12345678');
        $this->assertFalse($permit->isValid());
    }

    public function testHkMoValidNewFormat()
    {
        $permit = new MainlandToHkMoPermit('CA1234567');
        $this->assertTrue($permit->isValid());

        $permit = new MainlandToHkMoPermit('CB1234567');
        $this->assertTrue($permit->isValid());

        $permit = new MainlandToHkMoPermit('CZ1234567');
        $this->assertTrue($permit->isValid());
    }

    public function testHkMoNewFormatExcludesIO()
    {
        $permit = new MainlandToHkMoPermit('CI1234567');
        $this->assertFalse($permit->isValid());

        $permit = new MainlandToHkMoPermit('CO1234567');
        $this->assertFalse($permit->isValid());
    }

    public function testHkMoInvalidLength()
    {
        $permit = new MainlandToHkMoPermit('C1234567');
        $this->assertFalse($permit->isValid());

        $permit = new MainlandToHkMoPermit('');
        $this->assertFalse($permit->isValid());
    }

    public function testHkMoCompleteLastWildcard()
    {
        $results = iterator_to_array(MainlandToHkMoPermit::complete('C1234567*'));
        $this->assertCount(10, $results);

        foreach ($results as $result) {
            $permit = new MainlandToHkMoPermit($result);
            $this->assertTrue($permit->isValid());
        }
    }

    public function testHkMoCompleteLetterWildcard()
    {
        $results = iterator_to_array(MainlandToHkMoPermit::complete('*12345678'));
        $this->assertCount(2, $results);
        $this->assertSame('C12345678', $results[0]);
        $this->assertSame('W12345678', $results[1]);
    }

    public function testHkMoInstanceOf()
    {
        $permit = new MainlandToHkMoPermit('C12345678');
        $this->assertInstanceOf(IdInterface::class, $permit);
        $this->assertInstanceOf(MainlandIssuedInterface::class, $permit);
    }

    public function testTwValidL()
    {
        $permit = new MainlandToTwPermit('L12345678');
        $this->assertTrue($permit->isValid());
        $this->assertSame('L12345678', $permit->getCode());
    }

    public function testTwValidT()
    {
        $permit = new MainlandToTwPermit('T12345678');
        $this->assertTrue($permit->isValid());
        $this->assertSame('T12345678', $permit->getCode());
    }

    public function testTwValidLowerCase()
    {
        $permit = new MainlandToTwPermit('l12345678');
        $this->assertTrue($permit->isValid());

        $permit = new MainlandToTwPermit('t12345678');
        $this->assertTrue($permit->isValid());
    }

    public function testTwInvalidLetter()
    {
        $permit = new MainlandToTwPermit('H12345678');
        $this->assertFalse($permit->isValid());

        $permit = new MainlandToTwPermit('C12345678');
        $this->assertFalse($permit->isValid());
    }

    public function testTwInvalidLength()
    {
        $permit = new MainlandToTwPermit('L1234567');
        $this->assertFalse($permit->isValid());

        $permit = new MainlandToTwPermit('L123456789');
        $this->assertFalse($permit->isValid());

        $permit = new MainlandToTwPermit('');
        $this->assertFalse($permit->isValid());
    }

    public function testTwCompleteLastWildcard()
    {
        $results = iterator_to_array(MainlandToTwPermit::complete('L1234567*'));
        $this->assertCount(10, $results);

        foreach ($results as $result) {
            $permit = new MainlandToTwPermit($result);
            $this->assertTrue($permit->isValid());
        }
    }

    public function testTwCompleteLetterWildcard()
    {
        $results = iterator_to_array(MainlandToTwPermit::complete('*12345678'));
        $this->assertCount(2, $results);
        $this->assertSame('L12345678', $results[0]);
        $this->assertSame('T12345678', $results[1]);
    }

    public function testTwInstanceOf()
    {
        $permit = new MainlandToTwPermit('L12345678');
        $this->assertInstanceOf(IdInterface::class, $permit);
        $this->assertInstanceOf(MainlandIssuedInterface::class, $permit);
    }

    public function test_hkmo_get_type()
    {
        $permit = new MainlandToHkMoPermit('C12345678');
        $this->assertSame(IdType::MAINLAND_TO_HK_MO_PERMIT, $permit->getType());
    }

    public function test_tw_get_type()
    {
        $permit = new MainlandToTwPermit('L12345678');
        $this->assertSame(IdType::MAINLAND_TO_TW_PERMIT, $permit->getType());
    }

    public function test_hkmo_mask()
    {
        $permit = new MainlandToHkMoPermit('C12345678');
        $this->assertSame('C12****78', $permit->mask());
    }

    public function test_tw_mask()
    {
        $permit = new MainlandToTwPermit('L12345678');
        $this->assertSame('L12****78', $permit->mask());
    }
}
