<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotDisposableEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! str_contains($value, '@')) {
            return;
        }

        $domain = strtolower(substr(strrchr($value, '@'), 1));
        if ($domain === '') {
            return;
        }

        if (in_array($domain, config('email_blocklist', []), true)) {
            $fail('Disposable email addresses are not allowed. Please use a permanent email.');
        }
    }
}
