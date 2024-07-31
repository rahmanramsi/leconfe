<x-filament-panels::page>
    <div class="grid grid-cols-2 gap-4" wire:poll.2s>
        @foreach ($registrationTypes as $registrationType)
            <x-filament::section class=" shadow-xl outline outline-1 outline-gray-200">
                <h1 class="block font-semibold text-lg">{{ $registrationType->type }}</h1>
                <table class="w-full mt-2">
                    @if ($registrationType->currency === 'free' || $registrationType->cost <= 0)
                        <tr>
                            <td class="font-semibold">Cost</td>
                            <td>:</td>
                            <td class="text-center">Free</td>
                        </tr>
                    @else
                        <tr>
                            <td class="font-semibold">Cost</td>
                            <td>:</td>
                            <td class="text-center">{{ money($registrationType->cost, $registrationType->currency) }} ({{ currency($registrationType->currency)->getName() }})</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="font-semibold">Participant</td>
                        <td>:</td>
                        <td class="text-center">{{ $registrationType->getPaidParticipantCount() }}/{{ $registrationType->quota }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Expire</td>
                        <td>:</td>
                        <td class="text-center">{{ $registrationType->isExpired() ? 'Yes' : 'No' }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Active</td>
                        <td>:</td>
                        <td class="text-center">{{ $registrationType->active ? 'Yes' : 'No' }}</td>
                    </tr>
                </table>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
