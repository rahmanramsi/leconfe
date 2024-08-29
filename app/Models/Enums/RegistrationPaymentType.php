<?php

namespace App\Models\Enums;

use Illuminate\Support\Str;
use Filament\Support\Contracts\HasLabel;
use App\Models\Enums\Concern\UsefulEnums;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;

enum RegistrationPaymentType: string implements HasLabel
{
    use UsefulEnums;

    // Extendable
    case Manual = 'Manual';

    public function getLabel(): ?string
    {
        return $this->name;
    }
}
