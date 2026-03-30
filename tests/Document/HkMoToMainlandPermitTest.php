<?php

namespace HughCube\IdCard\Tests\Document;

use HughCube\IdCard\Contract\DocumentInterface;
use HughCube\IdCard\Contract\HongKongIssuedInterface;
use HughCube\IdCard\Contract\MacauIssuedInterface;
use HughCube\IdCard\Document\HkToMainlandPermit;
use HughCube\IdCard\Document\MoToMainlandPermit;
use PHPUnit\Framework\TestCase;

class HkMoToMainlandPermitTest extends TestCase
{
    /**
     * HkToMainlandPermit: 有效格式.
     */
    public function testHkValidFormat()
    {
        $permit = new HkToMainlandPermit('H12345678');
        $this->assertTrue($permit->isValid());
        $this->assertSame('H12345678', $permit->getCode());
    }

    /**
     * HkToMainlandPermit: 小写字母也合法.
     */
    public function testHkValidLowerCase()
    {
        $permit = new HkToMainlandPermit('h12345678');
        $this->assertTrue($permit->isValid());
    }

    /**
     * HkToMainlandPermit: 无效格式 - 字母错误.
     */
    public function testHkInvalidLetter()
    {
        $permit = new HkToMainlandPermit('M12345678');
        $this->assertFalse($permit->isValid());

        $permit = new HkToMainlandPermit('A12345678');
        $this->assertFalse($permit->isValid());
    }

    /**
     * HkToMainlandPermit: 无效格式 - 长度错误.
     */
    public function testHkInvalidLength()
    {
        $permit = new HkToMainlandPermit('H1234567');
        $this->assertFalse($permit->isValid());

        $permit = new HkToMainlandPermit('H123456789');
        $this->assertFalse($permit->isValid());

        $permit = new HkToMainlandPermit('');
        $this->assertFalse($permit->isValid());
    }

    /**
     * HkToMainlandPermit: complete 最后一位通配符应产出10个结果.
     */
    public function testHkCompleteLastWildcard()
    {
        $results = iterator_to_array(HkToMainlandPermit::complete('H1234567*'));
        $this->assertCount(10, $results);

        foreach ($results as $result) {
            $permit = new HkToMainlandPermit($result);
            $this->assertTrue($permit->isValid());
        }
    }

    /**
     * HkToMainlandPermit: instanceof 检查.
     */
    public function testHkInstanceOf()
    {
        $permit = new HkToMainlandPermit('H12345678');
        $this->assertInstanceOf(DocumentInterface::class, $permit);
        $this->assertInstanceOf(HongKongIssuedInterface::class, $permit);
    }

    /**
     * MoToMainlandPermit: 有效格式.
     */
    public function testMoValidFormat()
    {
        $permit = new MoToMainlandPermit('M12345678');
        $this->assertTrue($permit->isValid());
        $this->assertSame('M12345678', $permit->getCode());
    }

    /**
     * MoToMainlandPermit: 小写字母也合法.
     */
    public function testMoValidLowerCase()
    {
        $permit = new MoToMainlandPermit('m12345678');
        $this->assertTrue($permit->isValid());
    }

    /**
     * MoToMainlandPermit: 无效格式 - 字母错误.
     */
    public function testMoInvalidLetter()
    {
        $permit = new MoToMainlandPermit('H12345678');
        $this->assertFalse($permit->isValid());

        $permit = new MoToMainlandPermit('A12345678');
        $this->assertFalse($permit->isValid());
    }

    /**
     * MoToMainlandPermit: 无效格式 - 长度错误.
     */
    public function testMoInvalidLength()
    {
        $permit = new MoToMainlandPermit('M1234567');
        $this->assertFalse($permit->isValid());

        $permit = new MoToMainlandPermit('M123456789');
        $this->assertFalse($permit->isValid());

        $permit = new MoToMainlandPermit('');
        $this->assertFalse($permit->isValid());
    }

    /**
     * MoToMainlandPermit: complete 最后一位通配符应产出10个结果.
     */
    public function testMoCompleteLastWildcard()
    {
        $results = iterator_to_array(MoToMainlandPermit::complete('M1234567*'));
        $this->assertCount(10, $results);

        foreach ($results as $result) {
            $permit = new MoToMainlandPermit($result);
            $this->assertTrue($permit->isValid());
        }
    }

    /**
     * MoToMainlandPermit: instanceof 检查.
     */
    public function testMoInstanceOf()
    {
        $permit = new MoToMainlandPermit('M12345678');
        $this->assertInstanceOf(DocumentInterface::class, $permit);
        $this->assertInstanceOf(MacauIssuedInterface::class, $permit);
    }
}
