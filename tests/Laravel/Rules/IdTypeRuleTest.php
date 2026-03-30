<?php

namespace HughCube\IdCard\Tests\Laravel\Rules;

use HughCube\IdCard\IdType;
use HughCube\IdCard\Laravel\Rules\IdTypeRule;
use PHPUnit\Framework\TestCase;

class IdTypeRuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!interface_exists(\Illuminate\Contracts\Validation\ValidationRule::class)) {
            $this->markTestSkipped('illuminate/validation is not installed');
        }
    }

    public function test_make()
    {
        $rule = IdTypeRule::make();
        $this->assertInstanceOf(IdTypeRule::class, $rule);
    }

    public function test_valid_types()
    {
        $rule = IdTypeRule::make();

        foreach (IdType::all() as $type) {
            $failed = false;
            $rule->validate('id_type', $type, function () use (&$failed) {
                $failed = true;
            });
            $this->assertFalse($failed, "Type '{$type}' should be valid");
        }
    }

    public function test_invalid_types()
    {
        $rule = IdTypeRule::make();

        $invalidValues = ['', 'UNKNOWN', 123, null, true];
        foreach ($invalidValues as $value) {
            $failed = false;
            $rule->validate('id_type', $value, function () use (&$failed) {
                $failed = true;
            });
            $this->assertTrue($failed, 'Value '.var_export($value, true).' should be invalid');
        }
    }
}
