<div class="space-y-6">
    <div class="grid grid-cols-12 gap-4">
        <div class="space-y-4 col-span-8">
            @livewire(App\Panel\Livewire\Tables\Submissions\SubmissionFilesTable::class, ['record' => $submission, 'category' => 'submission-files', 'lazy' => true])
            @livewire(App\Panel\Livewire\Submissions\SubmissionDetail\Discussions::class, ['record' => $submission, 'lazy' => true])
        </div>
        <div class="self-start sticky top-24 flex flex-col gap-3 col-span-4">
            @if ($submission->accepted() && !$submission->reviewAssignments()->exists())
                <div class="bg-primary-700 p-4 rounded-lg text-base">
                    Your submission has been accepted. Now, we are waiting to next stage is open.
                </div>
            @elseif(!$submission->accepted())
                <div class="space-y-4">
                    {{ $this->acceptAction() }}
                    {{ $this->declineAction() }}
                </div>
            @endif
            @livewire(App\Panel\Livewire\Submissions\SubmissionDetail\AssignParticipants::class, ['submission' => $submission])
        </div>
    </div>
    <x-filament-actions::modals />
</div>
