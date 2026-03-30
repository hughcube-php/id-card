<?php

namespace HughCube\IdCard\Tests\Id;

use HughCube\IdCard\Contract\IdInterface;
use HughCube\IdCard\Id\MainlandId;
use HughCube\IdCard\IdType;
use HughCube\IdCard\Enum\GenderEnum;
use PHPUnit\Framework\TestCase;

class MainlandIdTest extends TestCase
{
    /**
     * 有效身份证号码.
     */
    public function testIsValidWithValidCode()
    {
        $id = new MainlandId('120112196405046337');
        $this->assertTrue($id->isValid());
    }

    /**
     * 无效身份证号码.
     */
    public function testIsValidWithInvalidCode()
    {
        $id = new MainlandId('420323199306066291');
        $this->assertFalse($id->isValid());
    }

    /**
     * 小写x的身份证号码.
     */
    public function testIsValidWithLowercaseX()
    {
        $id = new MainlandId('42032319930606629x');
        $this->assertTrue($id->isValid());
        $this->assertSame('42032319930606629X', $id->getCode());
    }

    /**
     * 获取出生日期.
     */
    public function testGetBirthday()
    {
        $id = new MainlandId('120112196405046337');
        $birthday = $id->getBirthday();
        $this->assertNotNull($birthday);
        $this->assertSame('1964-05-04', $birthday->format('Y-m-d'));
    }

    /**
     * 获取性别: 男.
     */
    public function testGetGenderMale()
    {
        $id = new MainlandId('120112196405046337');
        $this->assertSame(GenderEnum::MALE, $id->getGender());
    }

    /**
     * 获取性别: 女.
     */
    public function testGetGenderFemale()
    {
        $id = new MainlandId('120112196405046302');
        $this->assertTrue($id->isValid());
        $this->assertSame(GenderEnum::FEMALE, $id->getGender());
    }

    /**
     * 获取省份.
     */
    public function testGetProvince()
    {
        $id = new MainlandId('120112196405046337');
        $province = $id->getProvince();
        $this->assertSame('120000', $province->getValidCode());
        $this->assertSame('天津市', $province->getName());
    }

    /**
     * 获取城市.
     */
    public function testGetCity()
    {
        $id = new MainlandId('120112196405046337');
        $city = $id->getCity();
        $this->assertSame('120100', $city->getValidCode());
        $this->assertSame('市辖区', $city->getName());
    }

    /**
     * 获取区县.
     */
    public function testGetCounty()
    {
        $id = new MainlandId('120112196405046337');
        $county = $id->getCounty();
        $this->assertSame('120112', $county->getValidCode());
        $this->assertSame('津南区', $county->getName());
    }

    /**
     * 获取地区描述.
     */
    public function testGetAreaDescribe()
    {
        $id = new MainlandId('120112196405046337');
        $this->assertSame('天津市津南区', $id->getAreaDescribe());
    }

    /**
     * complete: 单个通配符.
     */
    public function testCompleteSingleWildcard()
    {
        $results = iterator_to_array(MainlandId::complete('12011219640504633*'), false);
        $this->assertContains('120112196405046337', $results);
    }

    /**
     * complete: 多个通配符.
     */
    public function testCompleteMultipleWildcards()
    {
        $results = iterator_to_array(MainlandId::complete('1201121964050463**'), false);
        $this->assertContains('120112196405046337', $results);
        $this->assertGreaterThan(1, count($results));

        foreach ($results as $code) {
            $id = new MainlandId($code);
            $this->assertTrue($id->isValid(), "Complete result {$code} should be valid");
        }
    }

    /**
     * instanceof 检查.
     */
    public function testInstanceOf()
    {
        $id = new MainlandId('120112196405046337');
        $this->assertInstanceOf(IdInterface::class, $id);
    }

    /**
     * complete: COMPLETE_AREA 模式下只返回有效地区码的结果.
     */
    public function testCompleteWithAreaMode()
    {
        $mode = MainlandId::COMPLETE_DEFAULT | MainlandId::COMPLETE_AREA;
        $results = iterator_to_array(MainlandId::complete('11****196405046337', $mode), false);

        $this->assertNotEmpty($results);
        foreach ($results as $code) {
            $id = new MainlandId($code);
            $this->assertTrue($id->isValid(), "Complete result {$code} should be valid");
            $this->assertTrue($id->getProvince()->isExists(), "Province should exist for {$code}");
        }
    }

    /**
     * complete: 无通配符时, 有效号码应返回自身.
     */
    public function testCompleteWithoutWildcard()
    {
        $results = iterator_to_array(MainlandId::complete('120112196405046337'), false);
        $this->assertCount(1, $results);
        $this->assertSame('120112196405046337', $results[0]);
    }

    /**
     * complete: 无通配符时, 无效号码不返回结果.
     */
    public function testCompleteWithoutWildcardInvalid()
    {
        $results = iterator_to_array(MainlandId::complete('420323199306066291'), false);
        $this->assertCount(0, $results);
    }

    /**
     * isValid: 使用 MODE_ALL 检查城市和县级.
     */
    public function testIsValidWithModeAll()
    {
        $id = new MainlandId('120112196405046337');
        $this->assertTrue($id->isValid(MainlandId::MODE_ALL));
    }

    /**
     * isValid: 仅检查格式.
     */
    public function testIsValidMatchOnly()
    {
        $id = new MainlandId('420323199306066291');
        $this->assertTrue($id->isValid(MainlandId::MODE_MATCH));
        $this->assertFalse($id->isValid(MainlandId::MODE_MATCH | MainlandId::MODE_FACTOR));
    }

    /**
     * 非字符串/格式错误输入.
     */
    public function testInvalidFormats()
    {
        $this->assertFalse((new MainlandId(''))->isValid());
        $this->assertFalse((new MainlandId('12345'))->isValid());
        $this->assertFalse((new MainlandId('abcdefghijklmnopqr'))->isValid());
    }

    /**
     * 测试 getType.
     */
    public function test_get_type()
    {
        $id = new MainlandId('120112196405046337');
        $this->assertSame(IdType::MAINLAND_ID, $id->getType());
    }

    /**
     * 测试 mask.
     */
    public function test_mask()
    {
        $id = new MainlandId('120112196405046337');
        $this->assertSame('120***********6337', $id->mask());

        // 无效长度返回 null
        $id2 = new MainlandId('12345');
        $this->assertNull($id2->mask());
    }
}
