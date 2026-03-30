<?php

namespace HughCube\IdCard\Id;

use Carbon\Carbon;
use Generator;
use HughCube\IdCard\Area;
use HughCube\IdCard\Contract\AreaAwareInterface;
use HughCube\IdCard\Contract\BirthdayAwareInterface;
use HughCube\IdCard\Contract\GenderAwareInterface;
use HughCube\IdCard\Contract\IdInterface;
use HughCube\IdCard\Contract\MainlandIssuedInterface;
use HughCube\IdCard\Data\AreaData;
use HughCube\IdCard\Enum\GenderEnum;
use HughCube\IdCard\IdType;

class MainlandId implements
    IdInterface,
    BirthdayAwareInterface,
    GenderAwareInterface,
    AreaAwareInterface,
    MainlandIssuedInterface
{
    const MODE_MATCH    = 1 << 0;
    const MODE_FACTOR   = 1 << 1;
    const MODE_PROVINCE = 1 << 2;
    const MODE_CITY     = 1 << 3;
    const MODE_COUNTY   = 1 << 4;
    const MODE_BIRTHDAY = 1 << 5;
    const MODE_GENDER   = 1 << 6;

    const MODE_ALL = 0
        | self::MODE_MATCH
        | self::MODE_FACTOR
        | self::MODE_PROVINCE
        | self::MODE_CITY
        | self::MODE_COUNTY
        | self::MODE_BIRTHDAY
        | self::MODE_GENDER;

    const MODE_DEFAULT = self::MODE_ALL ^ self::MODE_CITY ^ self::MODE_COUNTY;

    const COMPLETE_FACTOR = 1 << 0;
    const COMPLETE_BIRTHDAY = 1 << 1;
    const COMPLETE_AREA = 1 << 2;
    const COMPLETE_DEFAULT = self::COMPLETE_FACTOR | self::COMPLETE_BIRTHDAY;

    /**
     * @var string
     */
    protected $code;

    public function __construct(string $code)
    {
        $this->code = strtoupper($code);
    }

    public function getType(): string
    {
        return IdType::MAINLAND_ID;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function isValid(int $mode = self::MODE_DEFAULT): bool
    {
        $code = $this->code;

        if (($mode & self::MODE_MATCH) && 1 !== preg_match('/^\d{17}[\dX]$/', $code)) {
            return false;
        }

        if (($mode & self::MODE_FACTOR) && $code[17] !== self::calculateCheckDigit($code)) {
            return false;
        }

        if (($mode & self::MODE_PROVINCE) && !$this->getProvince()->isExists()) {
            return false;
        }

        if (($mode & self::MODE_CITY) && !$this->getCity()->isExists()) {
            return false;
        }

        if (($mode & self::MODE_COUNTY) && !$this->getCounty()->isExists()) {
            return false;
        }

        if (($mode & self::MODE_BIRTHDAY) && null === $this->getBirthday()) {
            return false;
        }

        if (($mode & self::MODE_GENDER) && null === $this->getGender()) {
            return false;
        }

        return true;
    }

    /**
     * 掩码: 保留前3位和后4位, 中间用*替换.
     */
    public function mask(): ?string
    {
        $code = $this->code;
        if (strlen($code) !== 18) {
            return null;
        }

        return substr($code, 0, 3) . str_repeat('*', 11) . substr($code, -4);
    }

    public function getBirthday(): ?Carbon
    {
        $code = $this->code;

        $year = (int) substr($code, 6, 4);
        $month = (int) substr($code, 10, 2);
        $day = (int) substr($code, 12, 2);

        if (!checkdate($month, $day, $year)) {
            return null;
        }

        return Carbon::createFromDate($year, $month, $day)->startOfDay();
    }

    public function getGender(): ?int
    {
        $code = $this->code;

        if (!isset($code[16]) || !is_numeric($code[16])) {
            return null;
        }

        $value = ((int) $code[16]) % 2 === 1 ? GenderEnum::MALE : GenderEnum::FEMALE;

        return GenderEnum::has($value) ? $value : null;
    }

    public function getProvince(): Area
    {
        return new Area(substr($this->code, 0, 2));
    }

    public function getCity(): Area
    {
        return new Area(substr($this->code, 0, 4));
    }

    public function getCounty(): Area
    {
        return new Area(substr($this->code, 0, 6));
    }

    public function getAreaDescribe(): string
    {
        $parts = [];

        $province = $this->getProvince();
        $name = $province->getName();
        if (null !== $name) {
            $parts[] = $name;
        }

        $city = $this->getCity();
        if (!$city->isPH()) {
            $name = $city->getName();
            if (null !== $name) {
                $parts[] = $name;
            }
        }

        $county = $this->getCounty();
        $name = $county->getName();
        if (null !== $name) {
            $parts[] = $name;
        }

        return implode('', $parts);
    }

    /**
     * @return Generator<string>
     */
    public static function complete(string $code, int $mode = self::COMPLETE_DEFAULT): Generator
    {
        $code = strtoupper($code);

        yield from static::completeRecursive($code, 0, $mode);
    }

    /**
     * 递归展开通配符.
     *
     * @return Generator<string>
     */
    protected static function completeRecursive(string $code, int $pos, int $mode): Generator
    {
        while ($pos < 18 && $code[$pos] !== '*') {
            $pos++;
        }

        if ($pos >= 18) {
            $instance = new self($code);
            if ($instance->isValid()) {
                yield $code;
            }
            return;
        }

        $candidates = static::getCandidates($code, $pos, $mode);

        foreach ($candidates as $digit) {
            $newCode = $code;
            $newCode[$pos] = $digit;

            if (($mode & self::COMPLETE_AREA) && $pos < 6) {
                $areaPos = $pos;
                if ($areaPos === 1) {
                    $provinceCode = substr($newCode, 0, 2) . '0000';
                    if (!AreaData::exists($provinceCode)) {
                        continue;
                    }
                } elseif ($areaPos === 3) {
                    $cityCode = substr($newCode, 0, 4) . '00';
                    if (!AreaData::exists($cityCode)) {
                        continue;
                    }
                } elseif ($areaPos === 5) {
                    $countyCode = substr($newCode, 0, 6);
                    if (!AreaData::exists($countyCode)) {
                        continue;
                    }
                }
            }

            if (($mode & self::COMPLETE_BIRTHDAY) && $pos >= 6 && $pos <= 13) {
                if (!static::isBirthdayPrefixValid($newCode, $pos)) {
                    continue;
                }
            }

            if ($pos === 17 && ($mode & self::COMPLETE_FACTOR)) {
                $check = static::calculateCheckDigit($newCode);
                $newCode[17] = $check;
                $instance = new self($newCode);
                if ($instance->isValid()) {
                    yield $newCode;
                }
                continue;
            }

            yield from static::completeRecursive($newCode, $pos + 1, $mode);
        }
    }

    protected static function getCandidates(string $code, int $pos, int $mode): array
    {
        if ($pos === 17) {
            if ($mode & self::COMPLETE_FACTOR) {
                $hasStar = false;
                for ($i = 0; $i < 17; $i++) {
                    if ($code[$i] === '*') {
                        $hasStar = true;
                        break;
                    }
                }
                if (!$hasStar) {
                    return [static::calculateCheckDigit($code)];
                }
            }
            return ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'X'];
        }

        return ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    }

    protected static function isBirthdayPrefixValid(string $code, int $pos): bool
    {
        if ($pos === 9) {
            $year = (int) substr($code, 6, 4);
            $currentYear = (int) date('Y');
            if ($year < 1900 || $year > $currentYear) {
                return false;
            }
        } elseif ($pos === 6) {
            $d = $code[6];
            if ($d !== '1' && $d !== '2') {
                return false;
            }
        } elseif ($pos === 7) {
            $prefix = substr($code, 6, 2);
            if ($prefix !== '19' && $prefix !== '20') {
                return false;
            }
        }

        if ($pos === 10) {
            $d = $code[10];
            if ($d !== '0' && $d !== '1') {
                return false;
            }
        } elseif ($pos === 11) {
            $month = (int) substr($code, 10, 2);
            if ($month < 1 || $month > 12) {
                return false;
            }
        }

        if ($pos === 12) {
            $d = $code[12];
            if ($d !== '0' && $d !== '1' && $d !== '2' && $d !== '3') {
                return false;
            }
        } elseif ($pos === 13) {
            $year = (int) substr($code, 6, 4);
            $month = (int) substr($code, 10, 2);
            $day = (int) substr($code, 12, 2);
            if (!checkdate($month, $day, $year)) {
                return false;
            }
        }

        return true;
    }

    protected static function calculateCheckDigit(string $code): string
    {
        $weights = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        $map = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];

        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            $sum += (int) $code[$i] * $weights[$i];
        }

        return $map[$sum % 11];
    }
}
