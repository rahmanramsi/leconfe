<?php

namespace App\Panel\Administration\Livewire;

use App\Facades\SidebarFacade;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Livewire\Component;

class SidebarSetting extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function render()
    {
        return view('panel.administration.livewire.sidebar-setting', $this->getViewData());
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'sidebars' => SidebarFacade::get(false)->map(fn ($sidebar) => [
                'id' => $sidebar->getId(),
                'name' => $sidebar->getName(),
                'isActive' => SidebarFacade::isActiveSidebar($sidebar),
                'suffixName' => $sidebar->getSuffixName(),
                'prefixName' => $sidebar->getPrefixName(),
            ]),
        ];
    }

    public function save($orderedItems)
    {
        SidebarFacade::updateActiveList($orderedItems);

        Notification::make()
            ->title(__('general.sidebar_updated'))
            ->success()
            ->send();

        return true;
    }
}
