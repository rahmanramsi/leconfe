<x-filament-panels::page @class([
    'fi-resource-list-records-page',
    'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
])>
    <div class="flex flex-col gap-y-6" x-data="{ activeTab: 'registrant-table' }">
        <x-filament::tabs>
            <x-filament::tabs.item alpine-active="activeTab === 'registrant-table'" 
                x-on:click="activeTab = 'registrant-table'">
                Registrant List
            </x-filament::tabs.item>
            <x-filament::tabs.item alpine-active="activeTab === 'type-summary'"
                x-on:click="activeTab = 'type-summary'">
                Registration Type
            </x-filament::tabs.item>
        </x-filament::tabs>

        <div x-show="activeTab === 'registrant-table'">
            {{-- <div class="grid grid-cols-12 gap-4"> --}}
            <div class="flex">
                @if (count($tabs = $this->getTabs()))
                    <x-filament::tabs class="!block !h-fit !ml-0 mr-6">
                        {{ \Filament\Support\Facades\FilamentView::renderHook('panels::resource.pages.list-records.tabs.start', scopes: $this->getRenderHookScopes()) }}

                        @foreach ($tabs as $tabKey => $tab)
                            @php
                                $activeTab = strval($activeTab);
                                $tabKey = strval($tabKey);
                            @endphp

                            <x-filament::tabs.item class="w-full" :active="$activeTab === $tabKey" :badge="$tab->getBadge()" :badgeColor="$tab->getBadgeColor()" :icon="$tab->getIcon()" :icon-position="$tab->getIconPosition()"
                                :wire:click="'$set(\'activeTab\', ' . (filled($tabKey) ? ('\'' . $tabKey . '\'') : 'null') . ')'">
                                {{ $tab->getLabel() ?? $this->generateTabLabel($tabKey) }}
                            </x-filament::tabs.item>
                        @endforeach

                        {{ \Filament\Support\Facades\FilamentView::renderHook('panels::resource.pages.list-records.tabs.end', scopes: $this->getRenderHookScopes()) }}
                    </x-filament::tabs>
                @endif
                <div class="w-full">
                    {{ \Filament\Support\Facades\FilamentView::renderHook('panels::resource.pages.list-records.table.before', scopes: $this->getRenderHookScopes()) }}

                    {{ $this->table }}

                    {{ \Filament\Support\Facades\FilamentView::renderHook('panels::resource.pages.list-records.table.after', scopes: $this->getRenderHookScopes()) }}
                </div>
            </div>
        </div>
        <div x-show="activeTab === 'type-summary'" style="display: none">
            @livewire(App\Panel\ScheduledConference\Livewire\Registration\RegistrationTypeSummary::class, ['lazy' => true])
        </div>
    </div>
</x-filament-panels::page>
