<?php

namespace DefaultSidebar\Sidebar;

use App\Classes\Sidebar;
use App\Panel\ScheduledConference\Resources\SubmissionResource\Pages\ManageSubmissions;
use App\Providers\PanelProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

class SubmitNowSidebar extends Sidebar
{
    protected ?string $view = 'DefaultSidebar::sidebar.submit-now';

    public function getId(): string
    {
        return 'submit-now';
    }

    public function getName(): string
    {
        return 'Submit Now';
    }

    public function render(): View
    {
        return view($this->view, $this->getViewData());
    }

    public function getViewData(): array
    {
        return [
            'id' => $this->getId(),
            'submissionUrl' => route(ManageSubmissions::getRouteName(PanelProvider::PANEL_SCHEDULED_CONFERENCE)),
        ];
    }
}
