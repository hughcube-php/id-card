<?php

namespace HughCube\IdCard\Tests\Id;

use HughCube\IdCard\Contract\IdInterface;
use HughCube\IdCard\Contract\TaiwanIssuedInterface;
use HughCube\IdCard\Id\TwToMainlandPermit;
use HughCube\IdCard\IdType;
use PHPUnit\Framework\TestCase;

class TwToMainlandPermitTest extends TestCase
{
    /**
     * 有效的台湾居民来往大陆通行证.
     */
    public function testValidCodes()
    {
        $permit = new TwToMainlandPermit('12345678');
        $this->assertTrue($permit->isValid());
        $this->assertSame('12345678', $permit->getCode());

        $permit = new TwToMainlandPermit('00000000');
        $this->assertTrue($permit->isValid());

        $permit = new TwToMainlandPermit('99999999');
        $this->assertTrue($permit->isValid());
    }

    /**
     * 无效格式.
     */
    public function testInvalidCodes()
    {
        // 太短
        $permit = new TwToMainlandPermit('1234567');
        $this->assertFalse($permit->isValid());

        // 太长
        $permit = new TwToMainlandPermit('123456789');
        $this->assertFalse($permit->isValid());

        // 含字母
        $permit = new TwToMainlandPermit('1234567A');
        $this->assertFalse($permit->isValid());

        // 空
        $permit = new TwToMainlandPermit('');
        $this->assertFalse($permit->isValid());
    }

    /**
     * 测试 getType.
     */
    public function test_get_type()
    {
        $permit = new TwToMainlandPermit('12345678');
        $this->assertSame(IdType::TW_TO_MAINLAND_PERMIT, $permit->getType());
    }

    /**
     * 测试 mask.
     */
    public function test_mask()
    {
        $permit = new TwToMainlandPermit('12345678');
        $this->assertSame('12****78', $permit->mask());

        $permit2 = new TwToMainlandPermit('1234');
        $this->assertNull($permit2->mask());
    }

    /**
     * 测试接口实现.
     */
    public function testInstanceOf()
    {
        $permit = new TwToMainlandPermit('12345678');
        $this->assertInstanceOf(IdInterface::class, $permit);
        $this->assertInstanceOf(TaiwanIssuedInterface::class, $permit);
    }

    /**
     * 测试 complete.
     */
    public function testComplete()
    {
        // 无通配符
        $results = iterator_to_array(TwToMainlandPermit::complete('12345678'));
        $this->assertCount(1, $results);
        $this->assertSame('12345678', $results[0]);

        // 最后一位通配符
        $results = iterator_to_array(TwToMainlandPermit::complete('1234567*'));
        $this->assertCount(10, $results);

        // 无效模式
        $results = iterator_to_array(TwToMainlandPermit::complete('123'));
        $this->assertCount(0, $results);
    }
}
