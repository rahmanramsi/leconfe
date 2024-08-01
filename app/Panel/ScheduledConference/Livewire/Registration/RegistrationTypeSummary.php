<?php

namespace App\Panel\ScheduledConference\Livewire\Registration;

use Livewire\Component;
use App\Models\RegistrationType;
use App\Panel\ScheduledConference\Resources\RegistrantResource;

class RegistrationTypeSummary extends Component
{
    protected static ?string $breadcrumb = 'Registration Type Stats';

    protected static string $resource = RegistrantResource::class;
    
    public static function getResource(): string
    {
        return static::$resource;
    }

    public function render()
    {
        return view('panel.scheduledConference.livewire.registrations.registration-type-summary');
    }
}
