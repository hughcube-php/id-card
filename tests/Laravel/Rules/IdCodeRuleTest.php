<?php

namespace HughCube\IdCard\Tests\Laravel\Rules;

use HughCube\IdCard\IdType;
use HughCube\IdCard\Laravel\Rules\IdCodeRule;
use Illuminate\Contracts\Validation\DataAwareRule;
use PHPUnit\Framework\TestCase;

class IdCodeRuleTest extends TestCase
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
        $rule = IdCodeRule::make();
        $this->assertInstanceOf(IdCodeRule::class, $rule);
        $this->assertInstanceOf(DataAwareRule::class, $rule);
    }

    public function test_make_with_custom_field()
    {
        $rule = IdCodeRule::make('type');
        $this->assertInstanceOf(IdCodeRule::class, $rule);
    }

    public function test_valid_mainland_id()
    {
        $rule = IdCodeRule::make();
        $rule->setData(['id_type' => IdType::MAINLAND_ID]);

        $failed = false;
        $rule->validate('id_code', '120112196405046337', function () use (&$failed) {
            $failed = true;
        });
        $this->assertFalse($failed);
    }

    public function test_invalid_mainland_id()
    {
        $rule = IdCodeRule::make();
        $rule->setData(['id_type' => IdType::MAINLAND_ID]);

        $failed = false;
        $rule->validate('id_code', '000000000000000000', function () use (&$failed) {
            $failed = true;
        });
        $this->assertTrue($failed);
    }

    public function test_skip_when_type_empty()
    {
        $rule = IdCodeRule::make();
        $rule->setData([]);

        $failed = false;
        $rule->validate('id_code', 'anything', function () use (&$failed) {
            $failed = true;
        });
        $this->assertFalse($failed, 'Should skip validation when type field is empty');
    }

    public function test_custom_type_field()
    {
        $rule = IdCodeRule::make('doc_type');
        $rule->setData(['doc_type' => IdType::TAIWAN_ID]);

        $failed = false;
        $rule->validate('id_code', 'A123456789', function () use (&$failed) {
            $failed = true;
        });
        $this->assertFalse($failed);
    }

    public function test_invalid_code_type()
    {
        $rule = IdCodeRule::make();
        $rule->setData(['id_type' => IdType::MAINLAND_ID]);

        $failed = false;
        $rule->validate('id_code', 12345, function () use (&$failed) {
            $failed = true;
        });
        $this->assertTrue($failed);
    }
}
