<?php

namespace App\Panel\ScheduledConference\Pages;

use App\Infolists\Components\LivewireEntry;
use App\Infolists\Components\VerticalTabs as InfolistsVerticalTabs;
use App\Panel\ScheduledConference\Livewire\AuthorRoleTable;
use App\Panel\ScheduledConference\Livewire\Workflows\AbstractSetting;
use App\Panel\ScheduledConference\Livewire\Workflows\EditingSetting;
use App\Panel\ScheduledConference\Livewire\Workflows\Payment\Tables\SubmissionPaymentItemTable;
use App\Panel\ScheduledConference\Livewire\Workflows\PaymentSetting;
use App\Panel\ScheduledConference\Livewire\Workflows\PeerReview\Forms\Guidelines;
use App\Panel\ScheduledConference\Livewire\Workflows\PeerReviewSetting;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;

class Workflow extends Page implements HasForms, HasInfolists
{
    use InteractsWithForms, InteractsWithInfolists;

    protected static ?int $navigationSort = 1;

    protected static string $view = 'panel.conference.pages.workflow';

    protected static ?string $navigationIcon = 'heroicon-o-window';

    protected static ?string $navigationGroup = 'Settings';

    public function mount()
    {   
        //
    }

    public function booted(): void
    {
        abort_if(! static::canView(), 403);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canView() && static::$shouldRegisterNavigation;
    }

    public static function canView(): bool
    {
        return Filament::auth()->user()->can('Workflow:update');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfolistsVerticalTabs\Tabs::make()
                ->tabs([
                    InfolistsVerticalTabs\Tab::make('Call for Abstract')
                        ->icon('iconpark-documentfolder-o')
                        ->schema([
                            Tabs::make()
                                ->tabs([
                                    Tabs\Tab::make('General')
                                        ->icon('iconpark-documentfolder-o')
                                        ->schema([
                                            LivewireEntry::make('abstract-setting')
                                                ->livewire(AbstractSetting::class),
                                        ]),
                                ]),
                        ]),
                    InfolistsVerticalTabs\Tab::make('Payment')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Tabs::make()
                                ->tabs([
                                    Tabs\Tab::make('General')
                                        ->schema([
                                            LivewireEntry::make('payment-setting')
                                                ->livewire(PaymentSetting::class),
                                        ]),
                                    Tabs\Tab::make('Submission Payment Items')
                                        ->schema([
                                            LivewireEntry::make('payment-items')
                                                ->livewire(SubmissionPaymentItemTable::class),
                                        ]),
                                ]),

                        ]),
                    InfolistsVerticalTabs\Tab::make('Peer Review')
                        ->icon('iconpark-search-o')
                        ->schema([
                            Tabs::make()
                                ->tabs([
                                    Tabs\Tab::make('General')
                                        ->icon('iconpark-documentfolder-o')
                                        ->schema([
                                            LivewireEntry::make('peer-review-setting')
                                                ->livewire(PeerReviewSetting::class)
                                                ->lazy(),
                                        ]),
                                    Tabs\Tab::make('Reviewer Guidelines')
                                        ->icon('iconpark-docsuccess-o')
                                        ->schema([
                                            LivewireEntry::make('peer-review-setting')
                                                ->livewire(Guidelines::class)
                                                ->lazy(),
                                        ]),
                                    // Tabs\Tab::make("Review Forms")
                                    //     ->icon("iconpark-formone-o")
                                    //     ->schema([
                                    //         LivewireEntry::make('peer-review-form-templates')
                                    //             ->livewire(FormTemplate::class)
                                    //             ->lazy()
                                    //     ])
                                ]),
                        ]),
                    InfolistsVerticalTabs\Tab::make('Editing')
                        ->icon('iconpark-paperclip')
                        ->schema([
                            Tabs::make()
                                ->tabs([
                                    Tabs\Tab::make('General')
                                        ->icon('iconpark-documentfolder-o')
                                        ->schema([
                                            LivewireEntry::make('editing-setting')
                                                ->livewire(EditingSetting::class)
                                                ->lazy(),
                                        ]),
                                ]),
                        ]),
                    InfolistsVerticalTabs\Tab::make('Advanced')
                        ->icon('heroicon-o-bookmark-square')
                        ->schema([
                            Tabs::make()
                                ->tabs([
                                    Tabs\Tab::make('Author Roles')
                                        ->icon('heroicon-o-users')
                                        ->extraAttributes(['class' => '!p-0'])
                                        ->schema([
                                            LivewireEntry::make('author-roles')
                                                ->livewire(AuthorRoleTable::class),
                                        ]),
                                ]),
                        ]),
                ])
                ->maxWidth('full'),
        ]);
    }
}
