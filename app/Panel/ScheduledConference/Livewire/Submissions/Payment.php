<?php

namespace App\Panel\ScheduledConference\Livewire\Submissions;

use App\Mail\Templates\AcceptAbstractMail;
use App\Mail\Templates\DeclineAbstractMail;
use App\Models\Enums\SubmissionStatus;
use App\Models\Enums\UserRole;
use App\Models\MailTemplate;
use App\Models\Role;
use App\Models\Submission;
use App\Notifications\AbstractAccepted;
use App\Notifications\AbstractDeclined;
use App\Panel\ScheduledConference\Resources\SubmissionResource;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use App\Forms\Components\TinyEditor;
use App\Models\Registration;
use App\Models\Timeline;

class Payment extends Component implements HasActions, HasForms
{
    use InteractsWithActions, InteractsWithForms;

    public Submission $submission;

    protected $listeners = [
        'refreshSubmission' => '$refresh',
    ];

    public function getViewData(): array
    {
        $user = auth()->user();
        $userRegistration = Registration::where('user_id', $user->id)->first();
        $registrationStatus = Timeline::isRegistrationOpen();

        return [
            'user' => $user,
            'userRegistration' => $userRegistration,
            'registrationStatus' => $registrationStatus,
        ];
    }

    public function render()
    {
        return view('panel.scheduledConference.livewire.submissions.payment', $this->getViewData());
    }
}
