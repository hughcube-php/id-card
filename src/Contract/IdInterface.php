<?php

namespace HughCube\IdCard\Contract;

use Carbon\Carbon;
use Generator;
use HughCube\IdCard\Area;

interface IdInterface
{
    public function getType(): string;

    public function getCode(): string;

    public function isValid(int $mode = 0): bool;

    public function mask(): ?string;

    /**
     * 标准化证件号码(去除分隔符、统一大小写等).
     */
    public static function normalize(string $code): string;

    /**
     * 返回验证用正则表达式.
     */
    public static function getPattern(): string;

    /**
     * 返回 complete 用正则表达式(支持通配符).
     */
    public static function getCompletePattern(): string;

    /**
     * @return Generator<string>
     */
    public static function complete(string $code, int $mode = 0): Generator;

    /**
     * 获取出生日期.
     */
    public function getBirthday(): ?Carbon;

    /**
     * 获取性别, 0=女, 1=男.
     */
    public function getGender(): ?int;

    /**
     * 获取省级区域.
     */
    public function getProvince(): ?Area;

    /**
     * 获取市级区域.
     */
    public function getCity(): ?Area;

    /**
     * 获取县级区域.
     */
    public function getCounty(): ?Area;

    /**
     * 获取地区描述字符串.
     */
    public function getAreaDescribe(): ?string;
}
