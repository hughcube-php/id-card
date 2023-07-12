<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/20
 * Time: 11:36 下午.
 */

namespace HughCube\IdCard\Tests;

use HughCube\IdCard\Checker;
use PHPUnit\Framework\TestCase;

class CheckerTest extends TestCase
{
    public function testIsAllowMode()
    {
        $checker = new Checker();

        $this->assertTrue(
            $checker->hasMode(Checker::MODE_CITY, Checker::MODE_ALL)
        );

        $this->assertFalse(
            $checker->hasMode(Checker::MODE_CITY, Checker::MODE_ALL ^ Checker::MODE_CITY)
        );

        $this->assertTrue(
            $checker->hasMode(Checker::MODE_COUNTY, Checker::MODE_ALL ^ Checker::MODE_CITY)
        );

        $this->assertTrue(
            $checker->hasMode(Checker::MODE_CITY, Checker::MODE_CITY | Checker::MODE_COUNTY)
        );
    }
}
