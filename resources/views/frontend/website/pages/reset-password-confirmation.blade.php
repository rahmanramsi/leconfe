<x-website::layouts.main class="space-y-2">
    <div class="mb-6">
        <x-website::breadcrumbs :breadcrumbs="$this->getBreadcrumbs()" />
    </div>
    <div class="relative">
        <div class="flex mb-5 space-x-4">
            <h1 class="text-xl font-semibold min-w-fit">{{ __('general.reset_password') }}</h1>
            <hr class="w-full h-px my-auto bg-gray-200 border-0 dark:bg-gray-700">
        </div>
        @if(!$success)
        <form wire:submit='submit' class="space-y-4">
            <p class="text-sm text-gray-700">
                {{ __('general.enter_password_to_update') }}
            </p>
            <div class="gap-2 form-control sm:col-span-6">
                <label class="label-text">
                    {{ __('general.new_password') }} <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password" class="input input-sm max-w-md" wire:model="password" required />
                @error('password')
                    <div class="text-sm text-red-600">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="gap-2 form-control sm:col-span-6">
                <label class="label-text">
                    {{ __('general.confirm_password') }} <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password_confirmation" class="input input-sm max-w-md" wire:model="password_confirmation" required />
                @error('password_confirmation')
                    <div class="text-sm text-red-600">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled">
                    <span class="loading loading-spinner loading-xs" wire:loading></span>
                    {{ __('general.submit') }}
                </button>
            </div>
        </form>
        @else
        <div class="space-y-4">
            <p class="text-sm text-gray-700">{{ __('general.reset_password_update_success') }}</p>
            <x-website::link class="btn btn-outline btn-sm" :href="app()->getLoginUrl()">
                {{ __('general.login') }}
            </x-website::link>
        </div>
        @endif

    </div>

</x-website::layouts.main>
