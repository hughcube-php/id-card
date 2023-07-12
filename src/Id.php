<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2022/9/16
 * Time: 22:10
 */

namespace HughCube\IdCard;

use Carbon\Carbon;
use HughCube\IdCard\Enum\GenderEnum;

class Id
{
    protected $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public static function parse($code): Id
    {
        /** @phpstan-ignore-next-line */
        return new static($code);
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getValidCode(): string
    {
        return strtoupper(strval($this->getCode()));
    }

    /**
     * 之所以加上mode参数, 是因为有些身份证的地区码是非常奇怪的,
     * 没办法收集全, 默认就不再检测城市和县级
     */
    public function isValid($mode = Checker::MODE_ALL ^ Checker::MODE_CITY ^ Checker::MODE_COUNTY): bool
    {
        $checker = new Checker($this);

        return $checker->isValid($mode);
    }

    /**
     * 身份证规则二, 前两位是省
     */
    public function getProvince(): Area
    {
        return new Area(substr($this->getValidCode(), 0, 2));
    }

    /**
     * 身份证规则二, 前四位是市
     */
    public function getCity(): Area
    {
        return new Area(substr($this->getValidCode(), 0, 4));
    }

    /**
     * 身份证规则二, 前四位是县
     */
    public function getCounty(): Area
    {
        return new Area(substr($this->getValidCode(), 0, 6));
    }

    public function getAreaDescribe(): string
    {
        $province = $this->getProvince();
        $city = $this->getCity();
        $county = $this->getCounty();

        $describe = '';

        if ($province->isExists()) {
            $describe = sprintf('%s%s', $describe, $province->getName());
        }

        if ($city->isExists() && (!$county->isExists() || !$city->isPH())) {
            $describe = sprintf('%s%s', $describe, $city->getName());
        }

        if ($county->isExists()) {
            $describe = sprintf('%s%s', $describe, $county->getName());
        }

        return $describe;
    }

    public function getBirthday(): ?Carbon
    {
        $code = $this->getValidCode();

        if (!checkdate(
            $m = substr($code, 10, 2),
            $d = substr($code, 12, 2),
            $y = substr($code, 6, 4)
        )) {
            return null;
        }

        return Carbon::parse(sprintf('%s-%s-%s 00:00:00.000', $y, $m, $d))->startOfDay();
    }

    /**
     * @see GenderEnum
     */
    public function getGender(): ?int
    {
        $code = substr($this->getValidCode(), 16, 1);

        $gender = intval($code) % 2;
        return (false !== $code && GenderEnum::has($gender)) ? $gender : null;
    }
}
