<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2022/9/16
 * Time: 22:10.
 */

namespace HughCube\IdCard;

use Carbon\Carbon;
use HughCube\IdCard\Document\MainlandId;

/**
 * @deprecated 请使用 Document\MainlandId 代替
 */
class Id
{
    protected $code;

    /**
     * @var MainlandId
     */
    protected $mainland;

    public function __construct($code)
    {
        $this->code = $code;
        $this->mainland = new MainlandId(strtoupper(strval($code)));
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
        return $this->mainland->getCode();
    }

    /**
     * 之所以加上mode参数, 是因为有些身份证的地区码是非常奇怪的,
     * 没办法收集全, 默认就不再检测城市和县级.
     */
    public function isValid($mode = Checker::MODE_ALL ^ Checker::MODE_CITY ^ Checker::MODE_COUNTY): bool
    {
        $checker = new Checker($this);

        return $checker->isValid($mode);
    }

    public function getProvince(): Area
    {
        return $this->mainland->getProvince();
    }

    public function getCity(): Area
    {
        return $this->mainland->getCity();
    }

    public function getCounty(): Area
    {
        return $this->mainland->getCounty();
    }

    public function getAreaDescribe(): string
    {
        return $this->mainland->getAreaDescribe();
    }

    public function getBirthday(): ?Carbon
    {
        return $this->mainland->getBirthday();
    }

    /**
     * @see \HughCube\IdCard\Enum\GenderEnum
     */
    public function getGender(): ?int
    {
        return $this->mainland->getGender();
    }
}
