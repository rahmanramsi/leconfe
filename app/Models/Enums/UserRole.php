<?php

namespace App\Models\Enums;

use App\Models\Enums\Concern\UsefulEnums;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    use UsefulEnums;

    case Admin = 'Admin';
    case ConferenceManager = 'Conference Manager';
    case SeriesManager = 'Series Manager';
    case Editor = 'Editor';
    case Reviewer = 'Reviewer';
    case Author = 'Author';

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public static function selfAssignedRoles(): array
    {
        return [
            UserRole::Reviewer,
            UserRole::Author,
        ];
    }

    public static function selfAssignedRoleNames(): array
    {
        return array_column(self::selfAssignedRoles(), 'name');
    }

    public static function selfAssignedRoleValues(): array
    {
        return array_column(self::selfAssignedRoles(), 'value');
    }

    public static function conferenceRoles(): array
    {
        return [
            static::ConferenceManager,
            static::SeriesManager,
            static::Editor,
            static::Reviewer,
            static::Author,
        ];
    }
}
