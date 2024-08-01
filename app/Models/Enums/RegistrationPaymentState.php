<?php

namespace App\Models\Enums;

use Illuminate\Support\Str;
use Filament\Support\Contracts\HasLabel;
use App\Models\Enums\Concern\UsefulEnums;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;

enum RegistrationPaymentState: string implements HasLabel, HasColor
{
    use UsefulEnums;

    case Paid = 'Paid';
    case Unpaid = 'Unpaid';

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Paid => Color::Green,
            self::Unpaid => Color::Yellow,
        };
    }
}
