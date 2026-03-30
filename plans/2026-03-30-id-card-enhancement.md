# ID Card Enhancement Implementation Plan

> **For agentic workers:** Use superpowers:subagent-driven-development to implement this plan task-by-task.

**Goal:** 为 hughcube/id-card 添加港澳台证件支持、号码补全功能，并更新地区数据。

**Architecture:** 接口分离 + 独立证件类 + 自动解析器。DocumentInterface 作为基础接口，BirthdayAwareInterface/GenderAwareInterface/AreaAwareInterface 作为能力接口，签发地标记接口用于分类。

**Tech Stack:** PHP >=7.1, Carbon, PHPUnit

---

### Task 1: 创建接口体系 (Contract/)

**Files:**
- Create: `src/Contract/DocumentInterface.php`
- Create: `src/Contract/BirthdayAwareInterface.php`
- Create: `src/Contract/GenderAwareInterface.php`
- Create: `src/Contract/AreaAwareInterface.php`
- Create: `src/Contract/MainlandIssuedInterface.php`
- Create: `src/Contract/HongKongIssuedInterface.php`
- Create: `src/Contract/MacauIssuedInterface.php`
- Create: `src/Contract/TaiwanIssuedInterface.php`

DocumentInterface 定义:
- `getCode(): string` — 原始证件号
- `isValid(): bool` — 校验
- `static complete(string $code, int $mode): Generator` — 补全

BirthdayAwareInterface: `getBirthday(): ?Carbon`
GenderAwareInterface: `getGender(): ?int`
AreaAwareInterface: `getProvince(): Area`, `getCity(): Area`, `getCounty(): Area`, `getAreaDescribe(): string`
四个签发地标记接口为空接口。

- [ ] Step 1: 创建 8 个接口文件
- [ ] Step 2: 运行 phpstan 验证语法
- [ ] Step 3: 提交

---

### Task 2: MainlandId 大陆身份证

**Files:**
- Create: `src/Document/MainlandId.php`
- Create: `tests/Document/MainlandIdTest.php`

实现 DocumentInterface, BirthdayAwareInterface, GenderAwareInterface, AreaAwareInterface, MainlandIssuedInterface。
从现有 Id.php + Checker.php 提取逻辑。包含 complete() 方法。

complete() 剪枝策略:
- 第1-6位: COMPLETE_AREA 开启时只展开有效地区前缀
- 第7-14位: COMPLETE_BIRTHDAY 开启时年1900-2026, 月01-12, 日checkdate
- 第15-17位: 0-9 全展开
- 第18位: COMPLETE_FACTOR 开启时前17位确定后直接计算，不穷举

COMPLETE 常量:
```php
const COMPLETE_FACTOR   = 1 << 0;
const COMPLETE_BIRTHDAY = 1 << 1;
const COMPLETE_AREA     = 1 << 2;
const COMPLETE_DEFAULT  = self::COMPLETE_FACTOR | self::COMPLETE_BIRTHDAY;
```

- [ ] Step 1: 编写 MainlandIdTest 测试（isValid, getBirthday, getGender, area, complete）
- [ ] Step 2: 实现 MainlandId
- [ ] Step 3: 运行测试确认通过
- [ ] Step 4: 提交

---

### Task 3: HongKongId 香港身份证

**Files:**
- Create: `src/Document/HongKongId.php`
- Create: `tests/Document/HongKongIdTest.php`

实现 DocumentInterface, HongKongIssuedInterface。

格式: `X999999(C)` 或 `XX999999(C)`, 括号可选
校验码算法: 加权 mod 11
- 单字母: 字母值(A=1..Z=26) × 8, 然后6位数字 × 7,6,5,4,3,2
- 双字母: 第一字母 × 9, 第二字母 × 8, 然后6位数字 × 7,6,5,4,3,2
- sum mod 11: 0→'0', 1→'A', 其余 11-remainder

complete(): 按格式规则 + 校验码剪枝

- [ ] Step 1: 编写测试
- [ ] Step 2: 实现
- [ ] Step 3: 运行测试
- [ ] Step 4: 提交

---

### Task 4: MacauId 澳门身份证

**Files:**
- Create: `src/Document/MacauId.php`
- Create: `tests/Document/MacauIdTest.php`

实现 DocumentInterface, MacauIssuedInterface。

格式: `N(NNNNNNN)C` 或 `NNNNNNNN`, 首位 1/5/7, 共 7 位数字 + 1 位校验码
校验码: 加权和 mod 11

complete(): 格式规则 + 校验码剪枝

- [ ] Step 1: 编写测试
- [ ] Step 2: 实现
- [ ] Step 3: 运行测试
- [ ] Step 4: 提交

---

### Task 5: TaiwanId 台湾身份证

**Files:**
- Create: `src/Document/TaiwanId.php`
- Create: `tests/Document/TaiwanIdTest.php`

实现 DocumentInterface, GenderAwareInterface, TaiwanIssuedInterface。

格式: `A123456789` — 1位字母 + 9位数字
第2位: 1=男, 2=女 (旧), 8/9 (2020年新式)
校验码: 字母→双位数(A=10..Z=35), 首位×1+次位×9, 然后8位×8,7,6,5,4,3,2,1, 末位×1, 总和 mod 10 == 0

额外方法: `getRegionCode(): string` 返回首字母对应的地区名

台湾地区码映射:
A=台北市, B=台中市, C=基隆市, D=台南市, E=高雄市, F=新北市,
G=宜兰县, H=桃园市, I=嘉义市, J=新竹县, K=苗栗县, L=台中县,
M=南投县, N=彰化县, O=新竹市, P=云林县, Q=嘉义县, R=台南县,
S=高雄县, T=屏东县, U=花莲县, V=台东县, W=金门县, X=澎湖县,
Y=阳明山, Z=连江县

- [ ] Step 1: 编写测试
- [ ] Step 2: 实现
- [ ] Step 3: 运行测试
- [ ] Step 4: 提交

---

### Task 6: HkToMainlandPermit + MoToMainlandPermit 回乡证

**Files:**
- Create: `src/Document/HkToMainlandPermit.php`
- Create: `src/Document/MoToMainlandPermit.php`
- Create: `tests/Document/HkMoToMainlandPermitTest.php`

HkToMainlandPermit 实现 DocumentInterface, HongKongIssuedInterface。
MoToMainlandPermit 实现 DocumentInterface, MacauIssuedInterface。

格式: `H` + 8位数字 (香港) / `M` + 8位数字 (澳门)
无公开校验码算法，isValid() 仅做格式校验。

- [ ] Step 1: 编写测试
- [ ] Step 2: 实现
- [ ] Step 3: 运行测试
- [ ] Step 4: 提交

---

### Task 7: MainlandToHkMoPermit + MainlandToTwPermit 通行证

**Files:**
- Create: `src/Document/MainlandToHkMoPermit.php`
- Create: `src/Document/MainlandToTwPermit.php`
- Create: `tests/Document/MainlandPermitTest.php`

MainlandToHkMoPermit 实现 DocumentInterface, MainlandIssuedInterface。
格式: `C` 或 `W` + 8位数字

MainlandToTwPermit 实现 DocumentInterface, MainlandIssuedInterface。
格式: `L` 或 `T` + 8位数字 (旧格式 T + 8位)

- [ ] Step 1: 编写测试
- [ ] Step 2: 实现
- [ ] Step 3: 运行测试
- [ ] Step 4: 提交

---

### Task 8: DocumentParser 自动识别

**Files:**
- Create: `src/DocumentParser.php`
- Create: `tests/DocumentParserTest.php`

`parse(string $code): ?DocumentInterface`

识别顺序（按格式特异性从高到低）:
1. 大陆身份证 (18位, 17数字+[0-9X])
2. 台湾身份证 (字母+9数字)
3. 香港身份证 (1-2字母+6数字+括号校验码)
4. 澳门身份证 ([157]开头+7-8位数字)
5. 回乡证 H/M + 8数字
6. 港澳通行证 C/W + 8数字
7. 台湾通行证 L/T + 8数字

每种先做格式匹配，匹配成功则创建实例返回。

- [ ] Step 1: 编写测试
- [ ] Step 2: 实现
- [ ] Step 3: 运行测试
- [ ] Step 4: 提交

---

### Task 9: 更新 Id.php 向后兼容

**Files:**
- Modify: `src/Id.php`

标记 @deprecated，内部组合 MainlandId。保持原有公开 API 不变。

- [ ] Step 1: 修改 Id.php
- [ ] Step 2: 运行原有测试确认通过
- [ ] Step 3: 提交

---

### Task 10: 更新 AreaData

**Files:**
- Create: `scripts/fetch-area-data.php`
- Modify: `src/Data/AreaData.php`

编写脚本从统计局网站抓取最新数据，与现有数据合并，生成新的 AreaData.php。

- [ ] Step 1: 编写抓取脚本
- [ ] Step 2: 运行抓取，合并数据
- [ ] Step 3: 运行全部测试确认不破坏
- [ ] Step 4: 提交

---

### Task 11: 最终验证

- [ ] Step 1: 运行全部测试
- [ ] Step 2: 运行 phpstan
- [ ] Step 3: 运行 check-style
- [ ] Step 4: 修复发现的问题
- [ ] Step 5: 最终提交
