<x-filament-widgets::widget>
    <div class="flex flex-wrap gap-4" wire:poll.2s>
        @foreach ($registrationTypes as $registrationType)
            <x-filament::section class="w-fit min-w-48">
                <h1 class="block font-semibold text-lg">{{ $registrationType->type }}</h1>
                <table class="w-full mt-2">
                    <tr>
                        <td class="font-semibold">Participant</td>
                        <td class="pl-1">:</td>
                        <td class="text-center">{{ $registrationType->getPaidParticipantCount() }}/{{ $registrationType->quota }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Expire</td>
                        <td class="pl-1">:</td>
                        <td class="text-center">{{ $registrationType->isExpired() ? 'Yes' : 'No' }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Active</td>
                        <td class="pl-1">:</td>
                        <td class="text-center">{{ $registrationType->active ? 'Yes' : 'No' }}</td>
                    </tr>
                </table>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-widgets::widget>
