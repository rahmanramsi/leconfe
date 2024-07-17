<?php

namespace App\Panel\ScheduledConference\Livewire\Workflows\PeerReview\Forms;

use App\Panel\ScheduledConference\Livewire\Workflows\Concerns\InteractWithTenant;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use App\Forms\Components\TinyEditor;
use Stevebauman\Purify\Facades\Purify;

class Guidelines extends \Livewire\Component implements HasForms
{
    use InteractsWithForms, InteractWithTenant;

    public string $reviewGuidelines;

    public string $competingInterests;

    public function mount(): void
    {
        $this->form->fill([
            'reviewGuidelines' => $this->scheduledConference->getMeta('review_guidelines', ''),
            'competingInterests' => $this->scheduledConference->getMeta('competing_interests', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TinyEditor::make('reviewGuidelines')
                    ->minHeight(300),
                TinyEditor::make('competingInterests')
                    ->minHeight(300),
            ]);
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $this->scheduledConference->setMeta('review_guidelines', Purify::clean($data['reviewGuidelines']));
        $this->scheduledConference->setMeta('competing_interests', Purify::clean($data['competingInterests']));

        Notification::make()
            ->title('Success!')
            ->body('The guidelines have been updated.')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('panel.scheduledConference.livewire.workflows.peer-review.forms.guidelines');
    }
}
