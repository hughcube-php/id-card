<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/20
 * Time: 11:36 下午.
 */

namespace HughCube\IdCard\Tests;

use HughCube\IdCard\IdCard;
use PHPUnit\Framework\TestCase;

class IdCardTest extends TestCase
{
    public function testSuccess()
    {
        $code = '42032319930606629x';

        $this->assertTrue(IdCard::check($code));
        $this->assertSame(1, IdCard::getGender($code));
        $this->assertSame('1993-06-06', IdCard::getBirthday($code));
    }

    public function testFailure()
    {
        $code = '420323199306066291';

        $this->assertFalse(IdCard::check($code));
        $this->assertSame(null, IdCard::getGender($code));
        $this->assertSame(null, IdCard::getBirthday($code));
    }
}
