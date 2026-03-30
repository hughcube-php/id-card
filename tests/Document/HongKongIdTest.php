<?php

namespace HughCube\IdCard\Tests\Document;

use HughCube\IdCard\Contract\DocumentInterface;
use HughCube\IdCard\Contract\HongKongIssuedInterface;
use HughCube\IdCard\Document\HongKongId;
use PHPUnit\Framework\TestCase;

class HongKongIdTest extends TestCase
{
    /**
     * G123456(A) 是有效号码.
     * sum = 36*9 + 16*8 + 1*7 + 2*6 + 3*5 + 4*4 + 5*3 + 6*2
     *     = 324 + 128 + 7 + 12 + 15 + 16 + 15 + 12 = 529
     * 529 mod 11 = 1, check = 11-1 = 10 = A
     */
    public function testValidSingleLetter()
    {
        $id = new HongKongId('G123456(A)');
        $this->assertTrue($id->isValid());
        $this->assertSame('G123456(A)', $id->getCode());
    }

    /**
     * 无效号码: 校验码错误.
     */
    public function testInvalidCheckDigit()
    {
        $id = new HongKongId('G123456(3)');
        $this->assertFalse($id->isValid());
    }

    /**
     * 双字母格式.
     * AB123456: sum = 10*9 + 11*8 + 1*7 + 2*6 + 3*5 + 4*4 + 5*3 + 6*2
     *         = 90 + 88 + 7 + 12 + 15 + 16 + 15 + 12 = 255
     * 255 mod 11 = 2, check = 11-2 = 9
     */
    public function testValidDoubleLetterWithBrackets()
    {
        $id = new HongKongId('AB123456(9)');
        $this->assertTrue($id->isValid());
        $this->assertSame('AB123456(9)', $id->getCode());
    }

    /**
     * 双字母不带括号.
     */
    public function testValidDoubleLetterWithoutBrackets()
    {
        $id = new HongKongId('AB1234569');
        $this->assertTrue($id->isValid());
    }

    /**
     * 单字母不带括号.
     */
    public function testValidSingleLetterWithoutBrackets()
    {
        $id = new HongKongId('G123456A');
        $this->assertTrue($id->isValid());
    }

    /**
     * 带括号格式.
     */
    public function testValidWithBrackets()
    {
        $id = new HongKongId('G123456(A)');
        $this->assertTrue($id->isValid());
    }

    /**
     * complete: 最后一位通配符应补全出正确校验码.
     */
    public function testCompleteLastWildcard()
    {
        $results = iterator_to_array(HongKongId::complete('G123456(*)'));
        $this->assertCount(1, $results);
        $this->assertSame('G123456(A)', $results[0]);
    }

    /**
     * complete: 不带括号的通配符.
     */
    public function testCompleteLastWildcardWithoutBrackets()
    {
        $results = iterator_to_array(HongKongId::complete('G123456*'));
        $this->assertCount(1, $results);
        $this->assertSame('G123456(A)', $results[0]);
    }

    /**
     * complete: 双字母最后一位通配符.
     */
    public function testCompleteDoubleLetterLastWildcard()
    {
        $results = iterator_to_array(HongKongId::complete('AB123456(*)'));
        $this->assertCount(1, $results);
        $this->assertSame('AB123456(9)', $results[0]);
    }

    /**
     * instanceof 检查.
     */
    public function testInstanceOf()
    {
        $id = new HongKongId('G123456(A)');
        $this->assertInstanceOf(DocumentInterface::class, $id);
        $this->assertInstanceOf(HongKongIssuedInterface::class, $id);
    }

    /**
     * 完全无效的格式.
     */
    public function testInvalidFormat()
    {
        $id = new HongKongId('12345678');
        $this->assertFalse($id->isValid());

        $id = new HongKongId('');
        $this->assertFalse($id->isValid());

        $id = new HongKongId('ABC123456(7)');
        $this->assertFalse($id->isValid());
    }

    /**
     * 校验码为 0 的情况: sum mod 11 == 0.
     * K000000: sum = 36*9 + 20*8 = 324 + 160 = 484, 484 mod 11 = 0, check = 0
     */
    public function testCheckDigitZero()
    {
        $id = new HongKongId('K000000(0)');
        $this->assertTrue($id->isValid());
    }

    /**
     * 使用已知真实数据验证算法正确性.
     * A123456(3): 来自 dev.to/samleung 的权威参考
     * sum = 36*9 + 10*8 + 1*7 + 2*6 + 3*5 + 4*4 + 5*3 + 6*2
     *     = 324 + 80 + 7 + 12 + 15 + 16 + 15 + 12 = 481
     * 481 mod 11 = 8, check = 11-8 = 3
     */
    public function testKnownRealWorldData()
    {
        $this->assertTrue((new HongKongId('A123456(3)'))->isValid());
    }
}
