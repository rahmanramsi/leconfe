<?php

use Akaunting\Money\Money;
use Illuminate\Support\Str;

if (! function_exists('moneyOrFree')) {
    function moneyOrFree(mixed $amount, ?string $currency = null, ?bool $convert = null): Money|string
    {
        if (Str::lower($currency) === 'free') {
            return 'Free';
        }

        return money(
            amount: $amount,
            currency: $currency,
            convert: $convert
        );
    }
}
