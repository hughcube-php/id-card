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
        // 将 Checker::MODE_* 映射到 MainlandId::MODE_*
        $mainlandMode = 0;

        if ($mode & Checker::MODE_TYPE) {
            // MODE_TYPE 检查是否为字符串, MainlandId 构造函数已强制 string, 这里手动检查
            if (!is_string($this->code)) {
                return false;
            }
        }

        if ($mode & Checker::MODE_MATCH) {
            $mainlandMode |= MainlandId::MODE_MATCH;
        }
        if ($mode & Checker::MODE_FACTOR) {
            $mainlandMode |= MainlandId::MODE_FACTOR;
        }
        if ($mode & Checker::MODE_PROVINCE) {
            $mainlandMode |= MainlandId::MODE_PROVINCE;
        }
        if ($mode & Checker::MODE_CITY) {
            $mainlandMode |= MainlandId::MODE_CITY;
        }
        if ($mode & Checker::MODE_COUNTY) {
            $mainlandMode |= MainlandId::MODE_COUNTY;
        }
        if ($mode & Checker::MODE_BIRTHDAY) {
            $mainlandMode |= MainlandId::MODE_BIRTHDAY;
        }
        if ($mode & Checker::MODE_GENDER) {
            $mainlandMode |= MainlandId::MODE_GENDER;
        }

        return $this->mainland->isValid($mainlandMode);
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
