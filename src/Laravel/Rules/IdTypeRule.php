<?php

namespace HughCube\IdCard\Laravel\Rules;

use Closure;
use HughCube\IdCard\IdType;
use Illuminate\Contracts\Validation\ValidationRule;

class IdTypeRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !IdType::has($value)) {
            $fail('证件类型无效');
        }
    }
}
