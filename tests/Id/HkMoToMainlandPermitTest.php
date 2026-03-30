<?php

namespace HughCube\IdCard\Tests\Id;

use HughCube\IdCard\Contract\IdInterface;
use HughCube\IdCard\Id\HkToMainlandPermit;
use HughCube\IdCard\Id\MoToMainlandPermit;
use HughCube\IdCard\IdType;
use PHPUnit\Framework\TestCase;

class HkMoToMainlandPermitTest extends TestCase
{
    public function testHkValidFormat()
    {
        $permit = new HkToMainlandPermit('H12345678');
        $this->assertTrue($permit->isValid());
        $this->assertSame('H12345678', $permit->getCode());
    }

    public function testHkValidLowerCase()
    {
        $permit = new HkToMainlandPermit('h12345678');
        $this->assertTrue($permit->isValid());
    }

    public function testHkInvalidLetter()
    {
        $permit = new HkToMainlandPermit('M12345678');
        $this->assertFalse($permit->isValid());

        $permit = new HkToMainlandPermit('A12345678');
        $this->assertFalse($permit->isValid());
    }

    public function testHkInvalidLength()
    {
        $permit = new HkToMainlandPermit('H1234567');
        $this->assertFalse($permit->isValid());

        $permit = new HkToMainlandPermit('');
        $this->assertFalse($permit->isValid());
    }

    public function testHkValidFullFormat()
    {
        $permit = new HkToMainlandPermit('H1234567800');
        $this->assertTrue($permit->isValid());

        $permit = new HkToMainlandPermit('H1234567801');
        $this->assertTrue($permit->isValid());
    }

    public function testHkInvalid10Digits()
    {
        $permit = new HkToMainlandPermit('H123456789');
        $this->assertFalse($permit->isValid());
    }

    public function testHkCompleteLastWildcard()
    {
        $results = iterator_to_array(HkToMainlandPermit::complete('H1234567*'));
        $this->assertCount(10, $results);

        foreach ($results as $result) {
            $permit = new HkToMainlandPermit($result);
            $this->assertTrue($permit->isValid());
        }
    }

    public function testHkInstanceOf()
    {
        $permit = new HkToMainlandPermit('H12345678');
        $this->assertInstanceOf(IdInterface::class, $permit);
    }

    public function testMoValidFormat()
    {
        $permit = new MoToMainlandPermit('M12345678');
        $this->assertTrue($permit->isValid());
        $this->assertSame('M12345678', $permit->getCode());
    }

    public function testMoValidLowerCase()
    {
        $permit = new MoToMainlandPermit('m12345678');
        $this->assertTrue($permit->isValid());
    }

    public function testMoInvalidLetter()
    {
        $permit = new MoToMainlandPermit('H12345678');
        $this->assertFalse($permit->isValid());

        $permit = new MoToMainlandPermit('A12345678');
        $this->assertFalse($permit->isValid());
    }

    public function testMoInvalidLength()
    {
        $permit = new MoToMainlandPermit('M1234567');
        $this->assertFalse($permit->isValid());

        $permit = new MoToMainlandPermit('');
        $this->assertFalse($permit->isValid());
    }

    public function testMoValidFullFormat()
    {
        $permit = new MoToMainlandPermit('M1234567800');
        $this->assertTrue($permit->isValid());
    }

    public function testMoCompleteLastWildcard()
    {
        $results = iterator_to_array(MoToMainlandPermit::complete('M1234567*'));
        $this->assertCount(10, $results);

        foreach ($results as $result) {
            $permit = new MoToMainlandPermit($result);
            $this->assertTrue($permit->isValid());
        }
    }

    public function testMoInstanceOf()
    {
        $permit = new MoToMainlandPermit('M12345678');
        $this->assertInstanceOf(IdInterface::class, $permit);
    }

    public function test_hk_get_type()
    {
        $permit = new HkToMainlandPermit('H12345678');
        $this->assertSame(IdType::HK_TO_MAINLAND_PERMIT, $permit->getType());
    }

    public function test_mo_get_type()
    {
        $permit = new MoToMainlandPermit('M12345678');
        $this->assertSame(IdType::MO_TO_MAINLAND_PERMIT, $permit->getType());
    }

    public function test_hk_mask()
    {
        $permit = new HkToMainlandPermit('H12345678');
        $this->assertSame('H12****78', $permit->mask());

        // 11位
        $permit2 = new HkToMainlandPermit('H1234567800');
        $this->assertSame('H12******00', $permit2->mask());
    }

    public function test_mo_mask()
    {
        $permit = new MoToMainlandPermit('M12345678');
        $this->assertSame('M12****78', $permit->mask());
    }
}
