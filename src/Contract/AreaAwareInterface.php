<?php

namespace HughCube\IdCard\Contract;

use HughCube\IdCard\Area;

interface AreaAwareInterface
{
    /**
     * 获取省级区域. 返回的 Area 对象可能不对应实际存在的区域, 需调用 isExists() 检查.
     */
    public function getProvince(): Area;

    /**
     * 获取市级区域. 返回的 Area 对象可能不对应实际存在的区域, 需调用 isExists() 检查.
     */
    public function getCity(): Area;

    /**
     * 获取县级区域. 返回的 Area 对象可能不对应实际存在的区域, 需调用 isExists() 检查.
     */
    public function getCounty(): Area;

    /**
     * 获取地区描述字符串, 如 "天津市津南区".
     */
    public function getAreaDescribe(): string;
}
