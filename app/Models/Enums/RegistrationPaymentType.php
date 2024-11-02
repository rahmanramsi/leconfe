<?php

namespace App\Models\Enums;

use App\Models\Enums\Concern\UsefulEnums;
use Filament\Support\Contracts\HasLabel;

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
