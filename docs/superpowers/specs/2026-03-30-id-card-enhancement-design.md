# ID Card Enhancement Design Spec

## Overview

对 `hughcube/id-card` 项目进行三项增强：更新地区数据、支持港澳台证件、添加号码补全功能。

---

## 1. AreaData 更新

### 数据来源
- **主要来源**：国家统计局最新年度县级以上行政区划代码
- **补充来源**：现有 AreaData.php 中的历史/废弃地区码

### 合并策略
- 统计局最新数据为基准
- 保留现有数据中统计局没有的码（历史废弃码，确保向后兼容）
- 按地区码数字排序
- 输出覆盖 `src/Data/AreaData.php`

### 抓取脚本
- 路径：`scripts/fetch-area-data.php`
- 用途：从统计局抓取并与现有数据合并，生成新的 AreaData.php

### 接口不变
- `AreaData::all()` / `AreaData::exists()` / `AreaData::name()` 保持原有签名

---

## 2. 接口体系

### 基础接口

```php
namespace HughCube\IdCard\Contract;

interface DocumentInterface
{
    public function getCode(): string;
    public function isValid(): bool;
    public static function complete(string $code, int $mode = self::COMPLETE_DEFAULT): \Generator;
}
```

### 能力接口

```php
interface BirthdayAwareInterface
{
    public function getBirthday(): ?\Carbon\Carbon;
}

interface GenderAwareInterface
{
    public function getGender(): ?int;
}

interface AreaAwareInterface
{
    public function getProvince(): \HughCube\IdCard\Area;
    public function getCity(): \HughCube\IdCard\Area;
    public function getCounty(): \HughCube\IdCard\Area;
}
```

### 签发地标记接口

```php
interface MainlandIssuedInterface {}
interface HongKongIssuedInterface {}
interface MacauIssuedInterface {}
interface TaiwanIssuedInterface {}
```

---

## 3. 证件类

### 各类实现的接口

| 类 | Document | Birthday | Gender | Area | 签发地标记 |
|----|----------|----------|--------|------|-----------|
| MainlandId（大陆身份证） | ✓ | ✓ | ✓ | ✓ | MainlandIssued |
| HongKongId（香港身份证） | ✓ | ✗ | ✗ | ✗ | HongKongIssued |
| MacauId（澳门身份证） | ✓ | ✗ | ✗ | ✗ | MacauIssued |
| TaiwanId（台湾身份证） | ✓ | ✗ | ✓ | ✗ | TaiwanIssued |
| HkMoToMainlandPermit（回乡证） | ✓ | ✓ | ✗ | ✗ | HongKongIssued 或 MacauIssued |
| MainlandToHkMoPermit（往来港澳通行证） | ✓ | ✗ | ✗ | ✗ | MainlandIssued |
| MainlandToTwPermit（往来台湾通行证） | ✓ | ✗ | ✗ | ✗ | MainlandIssued |

### 证件格式

- **大陆身份证 (MainlandId)**：18位，`/^[0-9]{17}[0-9X]$/i`
- **香港身份证 (HongKongId)**：如 `A123456(7)` 或 `AB123456(7)`，字母前缀+6位数字+校验码
- **澳门身份证 (MacauId)**：如 `1234567(8)` 或 `12345678`，7-8位数字+可选校验码
- **台湾身份证 (TaiwanId)**：如 `A123456789`，1位字母+9位数字，第2位 1=男 2=女
- **回乡证 (HkMoToMainlandPermit)**：如 `H12345678` 或 `M12345678`，H=香港 M=澳门，1位字母+8位数字（含出生日期）
- **往来港澳通行证 (MainlandToHkMoPermit)**：如 `C12345678`，1位字母+8位数字（2014年后卡式）
- **往来台湾通行证 (MainlandToTwPermit)**：如 `L12345678`，1位字母+8位数字（2015年后卡式）

### DocumentParser

```php
namespace HughCube\IdCard;

class DocumentParser
{
    /**
     * 按格式逐一尝试匹配，返回第一个匹配的实例。
     * 无法识别返回 null。
     */
    public static function parse(string $code): ?Contract\DocumentInterface;
}
```

调用方通过 `instanceof` 判断具体类型和能力：

```php
$doc = DocumentParser::parse($code);
if ($doc instanceof MainlandId) { ... }
if ($doc instanceof BirthdayAwareInterface) { $doc->getBirthday(); }
if ($doc instanceof MainlandIssuedInterface) { ... }
```

---

## 4. 号码补全

### 方法签名

```php
public static function complete(string $code, int $mode = self::COMPLETE_DEFAULT): \Generator;
```

`$code` 中 `*` 代表单个未知字符。返回 Generator 避免 OOM。

### 补全模式常量（定义在 DocumentInterface 上）

```php
const COMPLETE_FACTOR   = 1 << 0;  // 校验码过滤
const COMPLETE_BIRTHDAY = 1 << 1;  // 生日合法性过滤
const COMPLETE_AREA     = 1 << 2;  // 地区码过滤
const COMPLETE_DEFAULT  = self::COMPLETE_FACTOR | self::COMPLETE_BIRTHDAY;
```

### 大陆身份证剪枝策略

1. **第1-6位（地区码）**：开启 COMPLETE_AREA 时只展开 AreaData 中有效前缀，否则 0-9
2. **第7-14位（出生日期）**：年份 1900-2026，月份 01-12，日期按年月 checkdate 剪枝
3. **第15-17位（顺序码）**：0-9 全展开
4. **第18位（校验码）**：前17位确定后直接计算唯一值，不穷举

### 其他证件类型

各自根据自身校验规则实现剪枝，原理相同。无复杂校验规则的证件主要做格式层面过滤。

---

## 5. 文件结构

```
src/
├── Data/
│   └── AreaData.php                    # 更新后的地区数据
├── Enum/
│   └── GenderEnum.php                  # 不变
├── Contract/
│   ├── DocumentInterface.php
│   ├── BirthdayAwareInterface.php
│   ├── GenderAwareInterface.php
│   ├── AreaAwareInterface.php
│   ├── MainlandIssuedInterface.php
│   ├── HongKongIssuedInterface.php
│   ├── MacauIssuedInterface.php
│   └── TaiwanIssuedInterface.php
├── Document/
│   ├── MainlandId.php
│   ├── HongKongId.php
│   ├── MacauId.php
│   ├── TaiwanId.php
│   ├── HkMoToMainlandPermit.php
│   ├── MainlandToHkMoPermit.php
│   └── MainlandToTwPermit.php
├── DocumentParser.php
├── Area.php                            # 不变
├── Id.php                              # 保留，@deprecated，代理到 MainlandId
├── IdCard.php                          # 保留，已弃用
├── Checker.php                         # 保留，已弃用
└── Code.php                            # 保留，已弃用

scripts/
└── fetch-area-data.php
```

---

## 6. 向后兼容

- `Id.php`、`IdCard.php`、`Checker.php`、`Code.php` 全部保留不删除
- 现有 `AreaData` 接口不变
- 现有测试继续通过
