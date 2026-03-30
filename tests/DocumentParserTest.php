<?php

namespace HughCube\IdCard\Tests;

use HughCube\IdCard\Contract\AreaAwareInterface;
use HughCube\IdCard\Contract\BirthdayAwareInterface;
use HughCube\IdCard\Contract\GenderAwareInterface;
use HughCube\IdCard\Contract\HongKongIssuedInterface;
use HughCube\IdCard\Contract\MacauIssuedInterface;
use HughCube\IdCard\Contract\MainlandIssuedInterface;
use HughCube\IdCard\Contract\TaiwanIssuedInterface;
use HughCube\IdCard\Document\HkToMainlandPermit;
use HughCube\IdCard\Document\HongKongId;
use HughCube\IdCard\Document\MacauId;
use HughCube\IdCard\Document\MainlandId;
use HughCube\IdCard\Document\MainlandToHkMoPermit;
use HughCube\IdCard\Document\MainlandToTwPermit;
use HughCube\IdCard\Document\MoToMainlandPermit;
use HughCube\IdCard\Document\TaiwanId;
use HughCube\IdCard\DocumentParser;
use PHPUnit\Framework\TestCase;

class DocumentParserTest extends TestCase
{
    public function test_parse_mainland_id()
    {
        $doc = DocumentParser::parse('120112196405046337');
        $this->assertInstanceOf(MainlandId::class, $doc);
        $this->assertInstanceOf(BirthdayAwareInterface::class, $doc);
        $this->assertInstanceOf(GenderAwareInterface::class, $doc);
        $this->assertInstanceOf(AreaAwareInterface::class, $doc);
        $this->assertInstanceOf(MainlandIssuedInterface::class, $doc);
    }

    public function test_parse_mainland_id_lowercase_x()
    {
        $doc = DocumentParser::parse('42032319930606629x');
        $this->assertInstanceOf(MainlandId::class, $doc);
    }

    public function test_parse_taiwan_id()
    {
        $doc = DocumentParser::parse('A123456789');
        $this->assertInstanceOf(TaiwanId::class, $doc);
        $this->assertInstanceOf(GenderAwareInterface::class, $doc);
        $this->assertInstanceOf(TaiwanIssuedInterface::class, $doc);
    }

    public function test_parse_hongkong_id()
    {
        $doc = DocumentParser::parse('G123456(A)');
        $this->assertInstanceOf(HongKongId::class, $doc);
        $this->assertInstanceOf(HongKongIssuedInterface::class, $doc);
    }

    public function test_parse_macau_id()
    {
        // 需要一个有效的澳门身份证号
        // 10000003: 1*8+0*7+0*6+0*5+0*4+0*3+0*2+3*1 = 11, 11 mod 11 = 0 ✓
        $doc = DocumentParser::parse('10000003');
        $this->assertInstanceOf(MacauId::class, $doc);
        $this->assertInstanceOf(MacauIssuedInterface::class, $doc);
    }

    public function test_parse_hk_to_mainland_permit()
    {
        $doc = DocumentParser::parse('H12345678');
        $this->assertInstanceOf(HkToMainlandPermit::class, $doc);
        $this->assertInstanceOf(HongKongIssuedInterface::class, $doc);
    }

    public function test_parse_mo_to_mainland_permit()
    {
        $doc = DocumentParser::parse('M12345678');
        $this->assertInstanceOf(MoToMainlandPermit::class, $doc);
        $this->assertInstanceOf(MacauIssuedInterface::class, $doc);
    }

    public function test_parse_mainland_to_hkmo_permit()
    {
        $doc = DocumentParser::parse('C12345678');
        $this->assertInstanceOf(MainlandToHkMoPermit::class, $doc);
        $this->assertInstanceOf(MainlandIssuedInterface::class, $doc);

        $doc2 = DocumentParser::parse('W12345678');
        $this->assertInstanceOf(MainlandToHkMoPermit::class, $doc2);
    }

    public function test_parse_mainland_to_tw_permit()
    {
        $doc = DocumentParser::parse('L12345678');
        $this->assertInstanceOf(MainlandToTwPermit::class, $doc);
        $this->assertInstanceOf(MainlandIssuedInterface::class, $doc);

        $doc2 = DocumentParser::parse('T12345678');
        $this->assertInstanceOf(MainlandToTwPermit::class, $doc2);
    }

    public function test_parse_invalid()
    {
        $this->assertNull(DocumentParser::parse('INVALID'));
        $this->assertNull(DocumentParser::parse(''));
        $this->assertNull(DocumentParser::parse('123'));
    }

    /**
     * L12345678 应被识别为台湾通行证而非台湾身份证.
     * 台湾身份证格式是字母+9位数字(共10位), L12345678 只有9位.
     */
    public function test_parse_no_ambiguity_tw_permit_vs_tw_id()
    {
        $doc = DocumentParser::parse('L12345678');
        $this->assertInstanceOf(MainlandToTwPermit::class, $doc);
        $this->assertNotInstanceOf(TaiwanId::class, $doc);
    }

    /**
     * 8位纯数字不应被错误识别为大陆身份证.
     */
    public function test_parse_macau_not_mainland()
    {
        $doc = DocumentParser::parse('10000003');
        $this->assertInstanceOf(MacauId::class, $doc);
        $this->assertNotInstanceOf(MainlandId::class, $doc);
    }
}
