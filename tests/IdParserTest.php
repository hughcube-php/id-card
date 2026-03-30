<?php

namespace HughCube\IdCard\Tests;

use HughCube\IdCard\Id\HkToMainlandPermit;
use HughCube\IdCard\Id\HongKongId;
use HughCube\IdCard\Id\MacauId;
use HughCube\IdCard\Id\MainlandId;
use HughCube\IdCard\Id\MainlandToHkMoPermit;
use HughCube\IdCard\Id\MainlandToTwPermit;
use HughCube\IdCard\Id\MoToMainlandPermit;
use HughCube\IdCard\Id\TaiwanId;
use HughCube\IdCard\Id\TwToMainlandPermit;
use HughCube\IdCard\IdParser;
use HughCube\IdCard\IdType;
use PHPUnit\Framework\TestCase;

class IdParserTest extends TestCase
{
    public function test_parse_mainland_id()
    {
        $doc = IdParser::parse('120112196405046337');
        $this->assertInstanceOf(MainlandId::class, $doc);
    }

    public function test_parse_mainland_id_lowercase_x()
    {
        $doc = IdParser::parse('42032319930606629x');
        $this->assertInstanceOf(MainlandId::class, $doc);
    }

    public function test_parse_taiwan_id()
    {
        $doc = IdParser::parse('A123456789');
        $this->assertInstanceOf(TaiwanId::class, $doc);
    }

    public function test_parse_hongkong_id()
    {
        $doc = IdParser::parse('G123456(A)');
        $this->assertInstanceOf(HongKongId::class, $doc);
    }

    public function test_parse_macau_id()
    {
        $doc = IdParser::parse('10000003');
        $this->assertInstanceOf(MacauId::class, $doc);
    }

    public function test_parse_hk_to_mainland_permit()
    {
        $doc = IdParser::parse('H12345678');
        $this->assertInstanceOf(HkToMainlandPermit::class, $doc);
    }

    public function test_parse_mo_to_mainland_permit()
    {
        $doc = IdParser::parse('M12345678');
        $this->assertInstanceOf(MoToMainlandPermit::class, $doc);
    }

    public function test_parse_mainland_to_hkmo_permit()
    {
        $doc = IdParser::parse('C12345678');
        $this->assertInstanceOf(MainlandToHkMoPermit::class, $doc);

        $doc2 = IdParser::parse('W12345678');
        $this->assertInstanceOf(MainlandToHkMoPermit::class, $doc2);
    }

    public function test_parse_mainland_to_tw_permit()
    {
        $doc = IdParser::parse('L12345678');
        $this->assertInstanceOf(MainlandToTwPermit::class, $doc);

        $doc2 = IdParser::parse('T12345678');
        $this->assertInstanceOf(MainlandToTwPermit::class, $doc2);
    }

    public function test_parse_invalid()
    {
        $this->assertNull(IdParser::parse('INVALID'));
        $this->assertNull(IdParser::parse(''));
        $this->assertNull(IdParser::parse('123'));
    }

    public function test_parse_no_ambiguity_tw_permit_vs_tw_id()
    {
        $doc = IdParser::parse('L12345678');
        $this->assertInstanceOf(MainlandToTwPermit::class, $doc);
        $this->assertNotInstanceOf(TaiwanId::class, $doc);
    }

    public function test_parse_macau_not_mainland()
    {
        $doc = IdParser::parse('10000003');
        $this->assertInstanceOf(MacauId::class, $doc);
        $this->assertNotInstanceOf(MainlandId::class, $doc);
    }

    public function test_parse_new_format_hkmo_permit()
    {
        $doc = IdParser::parse('CA1234567');
        $this->assertInstanceOf(MainlandToHkMoPermit::class, $doc);
    }

    public function test_parse_full_format_home_return_permit()
    {
        $doc = IdParser::parse('H1234567800');
        $this->assertInstanceOf(HkToMainlandPermit::class, $doc);

        $doc = IdParser::parse('M1234567800');
        $this->assertInstanceOf(MoToMainlandPermit::class, $doc);
    }

    public function test_create()
    {
        $id = IdParser::create(IdType::MAINLAND_ID, '120112196405046337');
        $this->assertInstanceOf(MainlandId::class, $id);

        $id = IdParser::create(IdType::TW_TO_MAINLAND_PERMIT, '12345678');
        $this->assertInstanceOf(TwToMainlandPermit::class, $id);

        $this->assertNull(IdParser::create('nonexistent', '12345678'));
    }

    public function test_factory_methods()
    {
        $this->assertInstanceOf(MainlandId::class, IdParser::mainlandId('120112196405046337'));
        $this->assertInstanceOf(TaiwanId::class, IdParser::taiwanId('A123456789'));
        $this->assertInstanceOf(HongKongId::class, IdParser::hongKongId('G123456(A)'));
        $this->assertInstanceOf(MacauId::class, IdParser::macauId('10000003'));
        $this->assertInstanceOf(HkToMainlandPermit::class, IdParser::hkToMainlandPermit('H12345678'));
        $this->assertInstanceOf(MoToMainlandPermit::class, IdParser::moToMainlandPermit('M12345678'));
        $this->assertInstanceOf(MainlandToHkMoPermit::class, IdParser::mainlandToHkMoPermit('C12345678'));
        $this->assertInstanceOf(MainlandToTwPermit::class, IdParser::mainlandToTwPermit('L12345678'));
        $this->assertInstanceOf(TwToMainlandPermit::class, IdParser::twToMainlandPermit('12345678'));
    }
}
