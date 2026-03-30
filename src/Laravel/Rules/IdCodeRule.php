<?php

namespace HughCube\IdCard\Laravel\Rules;

use Closure;
use HughCube\IdCard\IdType;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class IdCodeRule implements ValidationRule, DataAwareRule
{
    protected array $data = [];

    protected string $typeField;

    public function __construct(string $typeField = 'id_type')
    {
        $this->typeField = $typeField;
    }

    public static function make(string $typeField = 'id_type'): self
    {
        return new self($typeField);
    }

    /**
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    public function validate(string $attribute, $value, Closure $fail): void
    {
        $type = $this->data[$this->typeField] ?? null;
        if (empty($type) || !is_string($type)) {
            return;
        }
        if (!is_string($value) || !IdType::isValid($type, $value)) {
            $fail('证件号码格式不正确');
        }
    }
}
