<?php

namespace App\Panel\ScheduledConference\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Infolists\Infolist;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Actions;
use Filament\Navigation\NavigationItem;
use Filament\Support\Enums\IconPosition;
use App\Actions\ScheduledConferences\ScheduledConferenceUpdateAction;
use Filament\Pages\SubNavigationPosition;
use App\Infolists\Components\LivewireEntry;
use Filament\Forms\Components\Actions\Action;
use App\Panel\ScheduledConference\Livewire\Payment\BankPayment;
use App\Forms\Components\TinyEditor;
use App\Infolists\Components\VerticalTabs as InfolistsVerticalTabs;

class PaymentSettings extends Page
{
    protected static string $view = 'panel.series.pages.payment-setting';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Payment';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Payment Method';

    public ?array $formData = [];

    public function mount(): void
    {
        $this->form->fill([
            'meta' => app()->getCurrentScheduledConference()->getAllMeta(),
        ]);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if($user->can('PaymentSetting:viewAny')) {
            return true;
        }
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TinyEditor::make('meta.payment_policy')
                    ->minHeight(450)
                    ->disabled(fn () =>  auth()->user()->cannot('PaymentSetting:edit')),
                Actions::make([
                    Action::make('save')
                        ->label('Save')
                        ->successNotificationTitle('Saved!')
                        ->failureNotificationTitle('Data could not be saved.')
                        ->action(function (Action $action) {
                            $formData = $this->form->getState();
                            try {
                                ScheduledConferenceUpdateAction::run(app()->getCurrentScheduledConference(), $formData);
                                $action->sendSuccessNotification();
                            } catch (\Throwable $th) {
                                $action->sendFailureNotification();
                                throw $th;
                            }
                        })
                        ->authorize('PaymentSetting:edit'),
                ])->alignRight(),
            ])
            ->statePath('formData');
    }

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }

    public function getSubNavigation(): array
    {
        return Payments::getPaymentSubNavigation();
    }

    public static function getNavigationItems(): array
    {
        return [];
    }
}
