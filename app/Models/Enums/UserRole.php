<?php

namespace App\Models\Enums;

use App\Models\Enums\Concern\UsefulEnums;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    use UsefulEnums;

    case Admin = 'Admin';
    case ConferenceManager = 'Conference Manager';
    case ScheduledConferenceEditor = 'Scheduled Conference Editor';
    case TrackEditor = 'Track Editor';
    case Reviewer = 'Reviewer';
    case Author = 'Author';
    case Reader = 'Reader';

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public static function selfAssignedRoles(): array
    {
        return [
            static::Reader,
            static::Author,
            static::Reviewer,
        ];
    }

    public static function selfAssignedRoleNames(): array
    {
        return array_column(self::selfAssignedRoles(), 'name', 'value');
    }

    public static function selfAssignedRoleValues(): array
    {
        return array_column(self::selfAssignedRoles(), 'value', 'name');
    }

    public static function conferenceRoles(): array
    {
        return [
            static::ConferenceManager,
            static::Reviewer,
            static::Author,
            static::Reader,
        ];
    }

    public static function scheduledConferenceRoles(): array 
    {
        return [
            static::ScheduledConferenceEditor,
            static::TrackEditor,
        ];
    }

    public static function internalRoles(): array
    {
        return [
            static::Admin,
            static::ConferenceManager,
            static::ScheduledConferenceEditor,
        ];
    }
}
