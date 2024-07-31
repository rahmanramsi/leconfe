<?php

namespace App\Models\Enums;

use App\Models\Enums\Concern\UsefulEnums;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SubmissionStatus: string implements HasColor, HasLabel
{
    use UsefulEnums;

    case Incomplete = 'Incomplete';
    case Queued = 'Queued';
    case OnReview = 'On Review';
    case OnPresentation = 'On Presentation';
    case Editing = 'Editing';
    case Published = 'Published';
    case Declined = 'Declined';
    case Withdrawn = 'Withdrawn';

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Declined, self::Withdrawn => 'danger',
            self::OnReview => 'warning',
            self::Queued => 'primary',
            self::Editing => 'info',
            self::Published => 'success',
            default => 'gray'
        };
    }
}
