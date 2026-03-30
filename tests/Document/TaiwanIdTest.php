<?php

namespace HughCube\IdCard\Tests\Document;

use HughCube\IdCard\Contract\DocumentInterface;
use HughCube\IdCard\Contract\GenderAwareInterface;
use HughCube\IdCard\Contract\TaiwanIssuedInterface;
use HughCube\IdCard\Document\TaiwanId;
use PHPUnit\Framework\TestCase;

class TaiwanIdTest extends TestCase
{
    /**
     * 测试有效的台湾身份证号码.
     */
    public function testValidId()
    {
        // A123456789: A=10, sum = 1*1+0*9+1*8+2*7+3*6+4*5+5*4+6*3+7*2+8*1+9*1 = 130, 130%10=0
        $id = new TaiwanId('A123456789');
        $this->assertTrue($id->isValid());
        $this->assertSame('A123456789', $id->getCode());

        // 测试小写也能通过验证
        $id2 = new TaiwanId('a123456789');
        $this->assertTrue($id2->isValid());
    }

    /**
     * 测试无效的台湾身份证号码.
     */
    public function testInvalidId()
    {
        // 修改校验码使其不合法
        $id = new TaiwanId('A123456781');
        $this->assertFalse($id->isValid());

        // 格式错误
        $id2 = new TaiwanId('1234567890');
        $this->assertFalse($id2->isValid());

        // 太短
        $id3 = new TaiwanId('A12345678');
        $this->assertFalse($id3->isValid());

        // 太长
        $id4 = new TaiwanId('A1234567890');
        $this->assertFalse($id4->isValid());
    }

    /**
     * 测试性别判断.
     */
    public function testGetGender()
    {
        // 第2位为1 -> 男(1)
        $id = new TaiwanId('A123456789');
        $this->assertSame(1, $id->getGender());

        // 第2位为2 -> 女(0)
        $id2 = new TaiwanId('A223456789');
        $this->assertSame(0, $id2->getGender());

        // 第2位为8 -> 男(1) (新式居留证)
        $id3 = new TaiwanId('A823456789');
        $this->assertSame(1, $id3->getGender());

        // 第2位为9 -> 女(0) (新式居留证)
        $id4 = new TaiwanId('A923456789');
        $this->assertSame(0, $id4->getGender());

        // 第2位为其他 -> null
        $id5 = new TaiwanId('A523456789');
        $this->assertNull($id5->getGender());
    }

    /**
     * 测试地区代码.
     */
    public function testGetRegionCode()
    {
        $id = new TaiwanId('A123456789');
        $this->assertSame('台北市', $id->getRegionCode());

        $id2 = new TaiwanId('B123456789');
        $this->assertSame('台中市', $id2->getRegionCode());

        $id3 = new TaiwanId('Z123456789');
        $this->assertSame('连江县', $id3->getRegionCode());
    }

    /**
     * 测试 complete 补全最后一位.
     */
    public function testComplete()
    {
        $results = iterator_to_array(TaiwanId::complete('A12345678*'));
        $this->assertCount(1, $results);
        $this->assertSame('A123456789', $results[0]);

        // 验证补全的结果确实合法
        $id = new TaiwanId($results[0]);
        $this->assertTrue($id->isValid());
    }

    /**
     * 测试 complete 补全非法模式返回空.
     */
    public function testCompleteInvalidPattern()
    {
        $results = iterator_to_array(TaiwanId::complete('123'));
        $this->assertCount(0, $results);
    }

    /**
     * 测试接口实现.
     */
    public function testInstanceOf()
    {
        $id = new TaiwanId('A123456789');
        $this->assertInstanceOf(DocumentInterface::class, $id);
        $this->assertInstanceOf(GenderAwareInterface::class, $id);
        $this->assertInstanceOf(TaiwanIssuedInterface::class, $id);
    }
}
