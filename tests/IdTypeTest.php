<?php

namespace HughCube\IdCard\Tests;

use HughCube\IdCard\IdType;
use PHPUnit\Framework\TestCase;

class IdTypeTest extends TestCase
{
    public function test_all()
    {
        $all = IdType::all();
        $this->assertCount(10, $all);
        $this->assertContains(IdType::MAINLAND_ID, $all);
        $this->assertContains(IdType::TAIWAN_ID, $all);
        $this->assertContains(IdType::HONG_KONG_ID, $all);
        $this->assertContains(IdType::MACAU_ID, $all);
        $this->assertContains(IdType::HK_TO_MAINLAND_PERMIT, $all);
        $this->assertContains(IdType::MO_TO_MAINLAND_PERMIT, $all);
        $this->assertContains(IdType::MAINLAND_TO_HK_MO_PERMIT, $all);
        $this->assertContains(IdType::MAINLAND_TO_TW_PERMIT, $all);
        $this->assertContains(IdType::TW_TO_MAINLAND_PERMIT, $all);
        $this->assertContains(IdType::NONE, $all);
    }

    public function test_has()
    {
        $this->assertTrue(IdType::has(IdType::MAINLAND_ID));
        $this->assertTrue(IdType::has(IdType::TW_TO_MAINLAND_PERMIT));
        $this->assertFalse(IdType::has('nonexistent'));
        $this->assertFalse(IdType::has(''));
    }

    public function test_title()
    {
        $this->assertSame('居民身份证', IdType::title(IdType::MAINLAND_ID));
        $this->assertSame('台湾身份证', IdType::title(IdType::TAIWAN_ID));
        $this->assertSame('香港身份证', IdType::title(IdType::HONG_KONG_ID));
        $this->assertSame('澳门身份证', IdType::title(IdType::MACAU_ID));
        $this->assertSame('港澳居民来往内地通行证(香港)', IdType::title(IdType::HK_TO_MAINLAND_PERMIT));
        $this->assertSame('澳门居民来往内地通行证', IdType::title(IdType::MO_TO_MAINLAND_PERMIT));
        $this->assertSame('往来港澳通行证', IdType::title(IdType::MAINLAND_TO_HK_MO_PERMIT));
        $this->assertSame('大陆居民往来台湾通行证', IdType::title(IdType::MAINLAND_TO_TW_PERMIT));
        $this->assertSame('台湾居民来往大陆通行证', IdType::title(IdType::TW_TO_MAINLAND_PERMIT));
        $this->assertSame('未设置', IdType::title(IdType::NONE));
        $this->assertNull(IdType::title('nonexistent'));
    }

    public function test_get_class()
    {
        $this->assertSame('HughCube\IdCard\Id\MainlandId', IdType::getClass(IdType::MAINLAND_ID));
        $this->assertSame('HughCube\IdCard\Id\TwToMainlandPermit', IdType::getClass(IdType::TW_TO_MAINLAND_PERMIT));
        $this->assertSame('HughCube\IdCard\Id\NoneId', IdType::getClass(IdType::NONE));
        $this->assertNull(IdType::getClass('nonexistent'));
    }

    public function test_is_valid()
    {
        $this->assertTrue(IdType::isValid(IdType::MAINLAND_ID, '120112196405046337'));
        $this->assertFalse(IdType::isValid(IdType::MAINLAND_ID, '420323199306066291'));
        $this->assertTrue(IdType::isValid(IdType::TAIWAN_ID, 'A123456789'));
        $this->assertTrue(IdType::isValid(IdType::HONG_KONG_ID, 'G123456(A)'));
        $this->assertTrue(IdType::isValid(IdType::MACAU_ID, '10000003'));
        $this->assertTrue(IdType::isValid(IdType::HK_TO_MAINLAND_PERMIT, 'H12345678'));
        $this->assertTrue(IdType::isValid(IdType::MO_TO_MAINLAND_PERMIT, 'M12345678'));
        $this->assertTrue(IdType::isValid(IdType::MAINLAND_TO_HK_MO_PERMIT, 'C12345678'));
        $this->assertTrue(IdType::isValid(IdType::MAINLAND_TO_TW_PERMIT, 'L12345678'));
        $this->assertTrue(IdType::isValid(IdType::TW_TO_MAINLAND_PERMIT, '12345678'));
        $this->assertFalse(IdType::isValid(IdType::NONE, 'anything'));
        $this->assertFalse(IdType::isValid('nonexistent', '12345678'));
    }

    public function test_mask()
    {
        $this->assertSame('120***********6337', IdType::mask(IdType::MAINLAND_ID, '120112196405046337'));
        $this->assertSame('A1****6789', IdType::mask(IdType::TAIWAN_ID, 'A123456789'));
        $this->assertSame('G1****6(A)', IdType::mask(IdType::HONG_KONG_ID, 'G123456(A)'));
        $this->assertSame('1*****03', IdType::mask(IdType::MACAU_ID, '10000003'));
        $this->assertSame('H12****78', IdType::mask(IdType::HK_TO_MAINLAND_PERMIT, 'H12345678'));
        $this->assertSame('M12****78', IdType::mask(IdType::MO_TO_MAINLAND_PERMIT, 'M12345678'));
        $this->assertSame('C12****78', IdType::mask(IdType::MAINLAND_TO_HK_MO_PERMIT, 'C12345678'));
        $this->assertSame('L12****78', IdType::mask(IdType::MAINLAND_TO_TW_PERMIT, 'L12345678'));
        $this->assertSame('12****78', IdType::mask(IdType::TW_TO_MAINLAND_PERMIT, '12345678'));
        $this->assertNull(IdType::mask(IdType::NONE, '12345678'));
        $this->assertNull(IdType::mask('nonexistent', '12345678'));
    }

}
