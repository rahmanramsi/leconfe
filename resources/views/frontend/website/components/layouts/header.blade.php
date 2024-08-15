@php
    $primaryNavigationItems = app()->getNavigationItems('primary-navigation-menu');
    $userNavigationMenu = app()->getNavigationItems('user-navigation-menu');
@endphp

<div class="navbar-publisher navbar-container bg-white shadow z-[51] text-gray-800 sticky top-0">
    <div class="navbar mx-auto max-w-7xl items-center h-full">
        <div class="navbar-start items-center gap-x-4 w-max">
            <x-website::link :href="url('')">
                <img
                    src="{{ app()->getSite()->getFirstMedia('logo')?->getAvailableUrl(['thumb', 'thumb-xl']) }}"
                    class="max-h-12 w-auto"
                />
            </x-website::link>
            @if(app()->getCurrentConference() || app()->getCurrentScheduledConference())
                @livewire(App\Livewire\GlobalNavigation::class)
            @endif

        </div>
        
        <div class="navbar-end ms-auto gap-x-4 hidden lg:inline-flex">
            <x-website::navigation-menu :items="$userNavigationMenu" class="text-gray-800" />
        </div>
    </div>
</div>
    
@if(app()->getCurrentConference() || app()->getCurrentScheduledConference())
    <div class="navbar-container bg-primary text-white shadow z-50">
        <div class="navbar mx-auto max-w-7xl justify-between">
            <div class="navbar-start items-center w-max gap-2">
                <x-website::navigation-menu-mobile />
                <x-website::logo :headerLogo="$headerLogo"/>
            </div>
            <div class="navbar-end hidden lg:flex relative z-10 w-max">
                <x-website::navigation-menu :items="$primaryNavigationItems" />
            </div>
        </div>
    </div>
@endif
