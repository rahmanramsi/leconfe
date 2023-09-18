<?php

namespace App\Models;

use App\Models\Scopes\AnnouncementScope;
use Filament\Facades\Filament;

class Announcement extends UserContent
{
    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope(new AnnouncementScope);
    }
}
