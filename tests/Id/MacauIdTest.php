<?php

namespace HughCube\IdCard\Tests\Id;

use HughCube\IdCard\Contract\IdInterface;
use HughCube\IdCard\Contract\MacauIssuedInterface;
use HughCube\IdCard\Id\MacauId;
use HughCube\IdCard\IdType;
use PHPUnit\Framework\TestCase;

class MacauIdTest extends TestCase
{
    public function testValidIds()
    {
        $id = new MacauId('10000003');
        $this->assertTrue($id->isValid());

        $id = new MacauId('50000004');
        $this->assertTrue($id->isValid());

        $id = new MacauId('70000018');
        $this->assertTrue($id->isValid());
    }

    public function testCheckDigitA()
    {
        $id = new MacauId('1000002A');
        $this->assertTrue($id->isValid());

        $id = new MacauId('1000002a');
        $this->assertTrue($id->isValid());

        $id = new MacauId('1000002(A)');
        $this->assertTrue($id->isValid());

        $id = new MacauId('1/000002/A');
        $this->assertTrue($id->isValid());
    }

    public function testInvalidIds()
    {
        $id = new MacauId('10000001');
        $this->assertFalse($id->isValid());

        $id = new MacauId('1000000');
        $this->assertFalse($id->isValid());

        $id = new MacauId('1000000B');
        $this->assertFalse($id->isValid());

        $id = new MacauId('');
        $this->assertFalse($id->isValid());
    }

    public function testInvalidFirstDigit()
    {
        $id = new MacauId('20000002');
        $this->assertFalse($id->isValid());

        $id = new MacauId('30000001');
        $this->assertFalse($id->isValid());

        $id = new MacauId('90000009');
        $this->assertFalse($id->isValid());
    }

    public function testSlashFormat()
    {
        $id = new MacauId('1/000000/3');
        $this->assertTrue($id->isValid());
        $this->assertSame('1/000000/3', $id->getCode());

        $id = new MacauId('5/000000/4');
        $this->assertTrue($id->isValid());
    }

    public function testGetCode()
    {
        $id = new MacauId('10000003');
        $this->assertSame('10000003', $id->getCode());

        $id = new MacauId('1/000000/3');
        $this->assertSame('1/000000/3', $id->getCode());
    }

    public function testComplete()
    {
        $results = iterator_to_array(MacauId::complete('10000003'));
        $this->assertContains('10000003', $results);
        $this->assertCount(1, $results);

        $results = iterator_to_array(MacauId::complete('1000000*'));
        $this->assertCount(1, $results);
        $this->assertContains('10000003', $results);

        $results = iterator_to_array(MacauId::complete('1000002*'));
        $this->assertCount(1, $results);
        $this->assertContains('1000002A', $results);

        $results = iterator_to_array(MacauId::complete('*0000003'));
        $this->assertContains('10000003', $results);
        foreach ($results as $r) {
            $this->assertTrue((bool) preg_match('/^[157]/', $r), "Should start with 1/5/7: {$r}");
        }

        $results = iterator_to_array(MacauId::complete('1/000000/*'));
        $this->assertCount(1, $results);
        $this->assertContains('10000003', $results);

        $results = iterator_to_array(MacauId::complete('10000001'));
        $this->assertCount(0, $results);
    }

    public function testParenthesisFormat()
    {
        $id = new MacauId('1000000(3)');
        $this->assertTrue($id->isValid());
        $this->assertSame('1000000(3)', $id->getCode());

        $id = new MacauId('5000000(4)');
        $this->assertTrue($id->isValid());
    }

    public function testInstanceOf()
    {
        $id = new MacauId('10000003');
        $this->assertInstanceOf(IdInterface::class, $id);
        $this->assertInstanceOf(MacauIssuedInterface::class, $id);
    }

    public function test_get_type()
    {
        $id = new MacauId('10000003');
        $this->assertSame(IdType::MACAU_ID, $id->getType());
    }

    public function test_mask()
    {
        $id = new MacauId('10000003');
        $this->assertSame('1*****03', $id->mask());

        $id2 = new MacauId('1/000000/3');
        $this->assertSame('1*****03', $id2->mask());

        $id3 = new MacauId('123');
        $this->assertNull($id3->mask());
    }
}
