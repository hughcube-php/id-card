<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/20
 * Time: 11:36 下午.
 */

namespace HughCube\IdCard\Tests;

use HughCube\IdCard\Checker;
use HughCube\IdCard\Id;
use PHPUnit\Framework\TestCase;

class IdTest extends TestCase
{
    public function test_parse()
    {
        $code = '42032319930606629x';

        $id = Id::parse($code);

        $this->assertInstanceOf(Id::class, $id);
        $this->assertSame($id->getValidCode(), strtoupper($code));
    }

    public function test_isValid()
    {
        $code = '420323199306066291';
        $this->assertFalse(Id::parse($code)->isValid());

        $code = '120112196405046337';
        $this->assertTrue(Id::parse($code)->isValid(Checker::MODE_ALL));
    }

    public function test_area()
    {
        $id = Id::parse('120112196405046337');

        $this->assertSame($id->getProvince()->getName(), '天津市');
        $this->assertSame($id->getCity()->getName(), '市辖区');
        $this->assertSame($id->getCounty()->getName(), '津南区');

        $this->assertSame($id->getAreaDescribe(), '天津市津南区');
    }

    public function test_getBirthday()
    {
        $id = Id::parse('120112196405046337');

        $this->assertSame($id->getBirthday()->format('Y-m-d'), '1964-05-04');
    }

    public function test_getGender()
    {
        $this->assertSame(Id::parse('120112196405046337')->getGender(), 1);
        $this->assertSame(Id::parse('120112196405046307')->getGender(), 0);
    }
}
