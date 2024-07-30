<x-filament-widgets::widget>
    <div class="relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="grid gap-y-2">
            <div class="flex items-center gap-x-2">
                <h1 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Current Scheduled Conference
                </h1>
            </div>

            <div class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                {{ $currentScheduledConference->title }}
            </div>

            <div class="flex items-center">
                <x-filament::button :href="$currentScheduledConference->getPanelUrl()" tag="a">
                    Open Scheduled Conference 
                </x-filament::button>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
