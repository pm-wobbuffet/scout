<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ArrayKeysAreNumericRule implements ValidationRule
{
    /**
     * Check to make sure that all keys in an array field are numeric
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            !is_array($value)
            || (array_keys($value) !== array_filter(array_keys($value), 'is_numeric'))
        ) {
            $fail('The :attribute must have only numeric keys');
        }
    }
}
