<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Models\CommitteeRole;
use Livewire\Attributes\Title;
use Rahmanramsi\LivewirePageGroup\Pages\Page;

class Committees extends Page
{
    protected static string $view = 'frontend.scheduledConference.pages.committees';

    public function mount()
    {
        //
    }

    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => 'Home',
            'Committees',
        ];
    }

    protected function getViewData(): array
    {
        $committeeRoles = CommitteeRole::query()
            ->whereHas('committees')
            ->with(['committees' => fn ($query) => $query->orderBy('order_column')])
            ->orderBy('order_column')
            ->get();

        return [
            'committeeRoles' => $committeeRoles,
        ];
    }
}
