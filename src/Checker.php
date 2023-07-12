<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2023/7/6
 * Time: 20:16.
 */

namespace HughCube\IdCard;

use Carbon\Carbon;
use HughCube\IdCard\Enum\GenderEnum;

class Checker
{
    const MODE_TYPE = 1 << 0;
    const MODE_MATCH = 1 << 1;
    const MODE_FACTOR = 1 << 2;
    const MODE_PROVINCE = 1 << 3;
    const MODE_CITY = 1 << 4;
    const MODE_COUNTY = 1 << 6;
    const MODE_BIRTHDAY = 1 << 7;
    const MODE_GENDER = 1 << 8;

    const MODE_ALL = 0
    | self::MODE_TYPE
    | self::MODE_MATCH
    | self::MODE_FACTOR
    | self::MODE_PROVINCE
    | self::MODE_CITY
    | self::MODE_COUNTY
    | self::MODE_BIRTHDAY
    | self::MODE_GENDER;

    protected $id;

    public function __construct(Id $id = null)
    {
        $this->id = $id;
    }

    public function hasMode($permission, $mode): bool
    {
        return ($mode & $permission) == $permission;
    }

    public function isValid($mode = Checker::MODE_ALL ^ Checker::MODE_CITY ^ Checker::MODE_COUNTY): bool
    {
        if ($this->hasMode(static::MODE_TYPE, $mode) && !$this->isValidType()) {
            return false;
        }

        if ($this->hasMode(static::MODE_MATCH, $mode) && !$this->isValidMatch()) {
            return false;
        }

        if ($this->hasMode(static::MODE_FACTOR, $mode) && !$this->isValidFactor()) {
            return false;
        }

        if ($this->hasMode(static::MODE_PROVINCE, $mode) && !$this->isValidProvince()) {
            return false;
        }

        if ($this->hasMode(static::MODE_CITY, $mode) && !$this->isValidCity()) {
            return false;
        }

        if ($this->hasMode(static::MODE_COUNTY, $mode) && !$this->isValidCounty()) {
            return false;
        }

        if ($this->hasMode(static::MODE_BIRTHDAY, $mode) && !$this->isValidBirthday()) {
            return false;
        }

        if ($this->hasMode(static::MODE_GENDER, $mode) && !$this->isValidGender()) {
            return false;
        }

        return true;
    }

    public function isValidType(): bool
    {
        return is_string($this->id->getCode());
    }

    public function isValidMatch(): bool
    {
        $code = $this->id->getValidCode();

        return 0 < preg_match('/^([0-9]{17}[0-9X])$/i', strtoupper($code));
    }

    /**
     * 身份证规则一, 把前十七位和系数相乘,然后相加对11取余,对应第十八位.
     */
    public function isValidFactor(): bool
    {
        $refer = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        $factor = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];

        $code = $this->id->getValidCode();

        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            $sum += intval(substr($code, $i, 1)) * $refer[$i];
        }

        return substr($code, 17, 1) == $factor[$sum % 11];
    }

    /**
     * 身份证规则二, 前两位是省
     */
    public function isValidProvince(): bool
    {
        return $this->id->getProvince()->isExists();
    }

    /**
     * 身份证规则二, 前四位是市
     */
    public function isValidCity(): bool
    {
        return $this->id->getCity()->isExists();
    }

    /**
     * 身份证规则二, 前四位是县
     */
    public function isValidCounty(): bool
    {
        return $this->id->getCounty()->isExists();
    }

    public function isValidBirthday(): bool
    {
        return $this->id->getBirthday() instanceof Carbon;
    }

    public function isValidGender(): bool
    {
        return GenderEnum::has($this->id->getGender());
    }
}
