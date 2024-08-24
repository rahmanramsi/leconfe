<?php

namespace DefaultSidebar\Sidebar;

use App\Classes\Sidebar;
use App\Frontend\ScheduledConference\Pages\ParticipantRegister;
use App\Panel\ScheduledConference\Resources\SubmissionResource\Pages\ManageSubmissions;
use App\Providers\PanelProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

class RegisterNowSidebar extends Sidebar
{
    protected ?string $view = 'DefaultSidebar::sidebar.register-now';

    public function getId(): string
    {
        return 'register-now';
    }

    public function getName(): string
    {
        return 'Register Now';
    }

    public function render(): View
    {
        return view($this->view, $this->getViewData());
    }

    public function getViewData(): array
    {
        return [
            'id' => $this->getId(),
            'registrationUrl' => route(ParticipantRegister::getRouteName('scheduledConference')),
        ];
    }
}
