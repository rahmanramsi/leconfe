<?php

use Akaunting\Money\Currency;
use Akaunting\Money\Money;

if (! function_exists('fixed_money')) {
    function fixed_money(mixed $amount, string $currency = null, bool $convert = null): Money | string
    {
        if(strcasecmp($currency, 'free') == 0) {
            return "Free";
        }

        return money(
            amount: $amount, 
            currency: $currency, 
            convert: $convert
        );
    }
}

if (! function_exists('fixed_currency')) {
    function fixed_currency(?string $currency = null): ?Currency
    {
        if(strcasecmp($currency, 'free') == 0) {
            return null;
        }

        return currency(
            currency: $currency,
        );
    }
}