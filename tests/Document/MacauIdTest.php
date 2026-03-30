<?php

namespace HughCube\IdCard\Tests\Document;

use HughCube\IdCard\Contract\DocumentInterface;
use HughCube\IdCard\Contract\MacauIssuedInterface;
use HughCube\IdCard\Document\MacauId;
use PHPUnit\Framework\TestCase;

class MacauIdTest extends TestCase
{
    /**
     * 测试有效的澳门身份证号码.
     */
    public function testValidIds()
    {
        // 10000013: 1*8+0*7+0*6+0*5+0*4+0*3+1*2+3*1 = 8+2+3 = 13, 不对
        // 重新算: 1*8+0*7+0*6+0*5+0*4+0*3+0*2+3*1 = 8+3 = 11, 11%11=0 ✓
        $id = new MacauId('10000003');
        $this->assertTrue($id->isValid());

        // 50000004: 5*8+0+0+0+0+0+0+4*1 = 40+4 = 44, 44%11=0 ✓
        $id = new MacauId('50000004');
        $this->assertTrue($id->isValid());

        // 70000018: 7*8+0+0+0+0+0+1*2+8*1 = 56+2+8 = 66, 66%11=0 ✓
        $id = new MacauId('70000018');
        $this->assertTrue($id->isValid());
    }

    /**
     * 测试校验码为 A 的情况.
     * 1000002: 1*8+0*7+0*6+0*5+0*4+0*3+2*2 = 12, 12%11=1, 11-1=10 → A
     */
    public function testCheckDigitA()
    {
        $id = new MacauId('1000002A');
        $this->assertTrue($id->isValid());

        // 小写 a 也合法
        $id = new MacauId('1000002a');
        $this->assertTrue($id->isValid());

        // 括号格式
        $id = new MacauId('1000002(A)');
        $this->assertTrue($id->isValid());

        // 斜杠格式
        $id = new MacauId('1/000002/A');
        $this->assertTrue($id->isValid());
    }

    /**
     * 测试无效号码.
     */
    public function testInvalidIds()
    {
        // 校验码错误
        $id = new MacauId('10000001');
        $this->assertFalse($id->isValid());

        // 长度不对
        $id = new MacauId('1000000');
        $this->assertFalse($id->isValid());

        // 非法字母 (B不是合法校验码)
        $id = new MacauId('1000000B');
        $this->assertFalse($id->isValid());

        // 空字符串
        $id = new MacauId('');
        $this->assertFalse($id->isValid());
    }

    /**
     * 测试首位非 1/5/7 为无效.
     */
    public function testInvalidFirstDigit()
    {
        // 20000002: 首位是2, 无效
        $id = new MacauId('20000002');
        $this->assertFalse($id->isValid());

        $id = new MacauId('30000001');
        $this->assertFalse($id->isValid());

        $id = new MacauId('90000009');
        $this->assertFalse($id->isValid());
    }

    /**
     * 测试带斜杠格式.
     */
    public function testSlashFormat()
    {
        // 1/000000/3 等效于 10000003
        $id = new MacauId('1/000000/3');
        $this->assertTrue($id->isValid());
        $this->assertSame('1/000000/3', $id->getCode());

        // 5/000000/4 等效于 50000004
        $id = new MacauId('5/000000/4');
        $this->assertTrue($id->isValid());
    }

    /**
     * 测试 getCode 返回原始证件号.
     */
    public function testGetCode()
    {
        $id = new MacauId('10000003');
        $this->assertSame('10000003', $id->getCode());

        $id = new MacauId('1/000000/3');
        $this->assertSame('1/000000/3', $id->getCode());
    }

    /**
     * 测试 complete 方法.
     */
    public function testComplete()
    {
        // 已知完整号码, 应该返回自身
        $results = iterator_to_array(MacauId::complete('10000003'));
        $this->assertContains('10000003', $results);
        $this->assertCount(1, $results);

        // 最后一位通配, 应直接计算校验码
        $results = iterator_to_array(MacauId::complete('1000000*'));
        $this->assertCount(1, $results);
        $this->assertContains('10000003', $results);

        // 校验码为 A 的 complete
        $results = iterator_to_array(MacauId::complete('1000002*'));
        $this->assertCount(1, $results);
        $this->assertContains('1000002A', $results);

        // 首位通配, 应展开 1/5/7
        $results = iterator_to_array(MacauId::complete('*0000003'));
        // 10000003 是有效的(如上), 检查是否包含
        $this->assertContains('10000003', $results);
        foreach ($results as $r) {
            $this->assertTrue((bool) preg_match('/^[157]/', $r), "Should start with 1/5/7: {$r}");
        }

        // 带斜杠输入, complete 也能处理
        $results = iterator_to_array(MacauId::complete('1/000000/*'));
        $this->assertCount(1, $results);
        $this->assertContains('10000003', $results);

        // 无效号码 complete 返回空
        $results = iterator_to_array(MacauId::complete('10000001'));
        $this->assertCount(0, $results);
    }

    /**
     * 测试括号格式: 1234567(8).
     */
    public function testParenthesisFormat()
    {
        $id = new MacauId('1000000(3)');
        $this->assertTrue($id->isValid());
        $this->assertSame('1000000(3)', $id->getCode());

        $id = new MacauId('5000000(4)');
        $this->assertTrue($id->isValid());
    }

    /**
     * 测试接口实现.
     */
    public function testInstanceOf()
    {
        $id = new MacauId('10000003');
        $this->assertInstanceOf(DocumentInterface::class, $id);
        $this->assertInstanceOf(MacauIssuedInterface::class, $id);
    }
}
