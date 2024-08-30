
<x-filament-tables::container class="overflow-x-auto">
    <form wire:submit='author-registration'>
        <table class="fi-ta-table w-full text-sm table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
            <thead class="divide-y divide-gray-200 dark:divide-white/5">
                <tr class="bg-gray-50 dark:bg-white/5 font-semibold">
                    <td class="px-6 py-3">{{ __('general.registration_type') }}</td>
                    <td class="px-6 py-3">{{ __('general.quota') }}</td>
                    <td class="px-6 py-3">{{ __('general.cost') }}</td>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                @foreach ($registrationTypeList as $index => $type)
                    <tr class="fi-ta-row">
                        <td class="px-6 py-3 fi-ta-cell">
                            <strong class="font-semibold">{{ $type->type }}</strong>
                            <p class="text-xs sm:text-sm">
                                {{ $type->getMeta('description') }}
                            </p>
                        </td>
                        <td class="px-6 py-3 fi-ta-cell">
                            {{ $type->getPaidParticipantCount() }}/{{ $type->quota }}
                        </td>
                        <td class="px-6 py-3 fi-ta-cell">
                            @php
                                $typeCostFormatted = moneyOrFree($type->cost, $type->currency, true);
                                $elementID = Str::slug($type->type)
                            @endphp
                            <div class="flex items-center gap-2">
                                <input 
                                    @class([
                                        'radio radio-xs radio-primary', 
                                        'cursor-pointer' => $type->isOpen()
                                    ]) 
                                    id="{{ $elementID }}" 
                                    type="radio" 
                                    wire:model="author-registration-type"
                                    name="authorRegistrationType"
                                    value="{{ $type->id }}"
                                    @disabled(!$type->isOpen())
                                />
                                <label @class(['cursor-pointer' => $type->isOpen()]) for="{{ $elementID }}">
                                    {{ $typeCostFormatted }}
                                </label>
                            </div>
                        </td>
                    </tr>
                @endforeach
                @if ($registrationTypeList->isEmpty())
                    <tr>
                        <td colspan="3" class="text-center">
                            {{ __('general.registration_type_are_empty') }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        <button class="btn btn-primary" wire:submit>Register</button>
    </form>
</x-filament-tables::table>