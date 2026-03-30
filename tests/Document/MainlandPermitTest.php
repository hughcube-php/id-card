<?php

namespace HughCube\IdCard\Tests\Document;

use HughCube\IdCard\Contract\DocumentInterface;
use HughCube\IdCard\Contract\MainlandIssuedInterface;
use HughCube\IdCard\Document\MainlandToHkMoPermit;
use HughCube\IdCard\Document\MainlandToTwPermit;
use PHPUnit\Framework\TestCase;

class MainlandPermitTest extends TestCase
{
    /**
     * MainlandToHkMoPermit: 有效格式 - 卡式(C).
     */
    public function testHkMoValidC()
    {
        $permit = new MainlandToHkMoPermit('C12345678');
        $this->assertTrue($permit->isValid());
        $this->assertSame('C12345678', $permit->getCode());
    }

    /**
     * MainlandToHkMoPermit: 有效格式 - 本式(W).
     */
    public function testHkMoValidW()
    {
        $permit = new MainlandToHkMoPermit('W12345678');
        $this->assertTrue($permit->isValid());
        $this->assertSame('W12345678', $permit->getCode());
    }

    /**
     * MainlandToHkMoPermit: 小写字母也合法.
     */
    public function testHkMoValidLowerCase()
    {
        $permit = new MainlandToHkMoPermit('c12345678');
        $this->assertTrue($permit->isValid());

        $permit = new MainlandToHkMoPermit('w12345678');
        $this->assertTrue($permit->isValid());
    }

    /**
     * MainlandToHkMoPermit: 无效格式 - 字母错误.
     */
    public function testHkMoInvalidLetter()
    {
        $permit = new MainlandToHkMoPermit('H12345678');
        $this->assertFalse($permit->isValid());

        $permit = new MainlandToHkMoPermit('L12345678');
        $this->assertFalse($permit->isValid());
    }

    /**
     * MainlandToHkMoPermit: 无效格式 - 长度错误.
     */
    public function testHkMoInvalidLength()
    {
        $permit = new MainlandToHkMoPermit('C1234567');
        $this->assertFalse($permit->isValid());

        $permit = new MainlandToHkMoPermit('C123456789');
        $this->assertFalse($permit->isValid());

        $permit = new MainlandToHkMoPermit('');
        $this->assertFalse($permit->isValid());
    }

    /**
     * MainlandToHkMoPermit: complete 最后一位通配符应产出10个结果.
     */
    public function testHkMoCompleteLastWildcard()
    {
        $results = iterator_to_array(MainlandToHkMoPermit::complete('C1234567*'));
        $this->assertCount(10, $results);

        foreach ($results as $result) {
            $permit = new MainlandToHkMoPermit($result);
            $this->assertTrue($permit->isValid());
        }
    }

    /**
     * MainlandToHkMoPermit: complete 字母位通配符应展开为 C 和 W.
     */
    public function testHkMoCompleteLetterWildcard()
    {
        $results = iterator_to_array(MainlandToHkMoPermit::complete('*12345678'));
        $this->assertCount(2, $results);
        $this->assertSame('C12345678', $results[0]);
        $this->assertSame('W12345678', $results[1]);
    }

    /**
     * MainlandToHkMoPermit: instanceof 检查.
     */
    public function testHkMoInstanceOf()
    {
        $permit = new MainlandToHkMoPermit('C12345678');
        $this->assertInstanceOf(DocumentInterface::class, $permit);
        $this->assertInstanceOf(MainlandIssuedInterface::class, $permit);
    }

    /**
     * MainlandToTwPermit: 有效格式 - 卡式(L).
     */
    public function testTwValidL()
    {
        $permit = new MainlandToTwPermit('L12345678');
        $this->assertTrue($permit->isValid());
        $this->assertSame('L12345678', $permit->getCode());
    }

    /**
     * MainlandToTwPermit: 有效格式 - 本式(T).
     */
    public function testTwValidT()
    {
        $permit = new MainlandToTwPermit('T12345678');
        $this->assertTrue($permit->isValid());
        $this->assertSame('T12345678', $permit->getCode());
    }

    /**
     * MainlandToTwPermit: 小写字母也合法.
     */
    public function testTwValidLowerCase()
    {
        $permit = new MainlandToTwPermit('l12345678');
        $this->assertTrue($permit->isValid());

        $permit = new MainlandToTwPermit('t12345678');
        $this->assertTrue($permit->isValid());
    }

    /**
     * MainlandToTwPermit: 无效格式 - 字母错误.
     */
    public function testTwInvalidLetter()
    {
        $permit = new MainlandToTwPermit('H12345678');
        $this->assertFalse($permit->isValid());

        $permit = new MainlandToTwPermit('C12345678');
        $this->assertFalse($permit->isValid());
    }

    /**
     * MainlandToTwPermit: 无效格式 - 长度错误.
     */
    public function testTwInvalidLength()
    {
        $permit = new MainlandToTwPermit('L1234567');
        $this->assertFalse($permit->isValid());

        $permit = new MainlandToTwPermit('L123456789');
        $this->assertFalse($permit->isValid());

        $permit = new MainlandToTwPermit('');
        $this->assertFalse($permit->isValid());
    }

    /**
     * MainlandToTwPermit: complete 最后一位通配符应产出10个结果.
     */
    public function testTwCompleteLastWildcard()
    {
        $results = iterator_to_array(MainlandToTwPermit::complete('L1234567*'));
        $this->assertCount(10, $results);

        foreach ($results as $result) {
            $permit = new MainlandToTwPermit($result);
            $this->assertTrue($permit->isValid());
        }
    }

    /**
     * MainlandToTwPermit: complete 字母位通配符应展开为 L 和 T.
     */
    public function testTwCompleteLetterWildcard()
    {
        $results = iterator_to_array(MainlandToTwPermit::complete('*12345678'));
        $this->assertCount(2, $results);
        $this->assertSame('L12345678', $results[0]);
        $this->assertSame('T12345678', $results[1]);
    }

    /**
     * MainlandToTwPermit: instanceof 检查.
     */
    public function testTwInstanceOf()
    {
        $permit = new MainlandToTwPermit('L12345678');
        $this->assertInstanceOf(DocumentInterface::class, $permit);
        $this->assertInstanceOf(MainlandIssuedInterface::class, $permit);
    }
}
