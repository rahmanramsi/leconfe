<div class="space-y-6">
  @if($stageOpened && $submission->accepted())
    <div class="grid grid-cols-12 gap-4">
      <div class="space-y-4 col-span-8">
          @livewire(App\Panel\Livewire\Tables\Submissions\SubmissionFilesTable::class, ['record' => $submission, 'category' => 'submission-papers'])
          @livewire(App\Panel\Livewire\Submissions\Components\ReviewerList::class, ['record' => $submission])
          @livewire(App\Panel\Livewire\Submissions\SubmissionDetail\Discussions::class, ['record' => $submission])
      </div>
      <div class="self-start sticky top-24 flex flex-col gap-3 col-span-4">
        {{ $this->skipReviewAction }}
        {{-- {{ $this->acceptAction() }}
        {{ $this->requestRevisionAction() }}
        {{ $this->suggestAcceptAction() }} --}}
      </div>
    </div>
  <x-filament-actions::modals />
  @elseif(!$submission->accepted())
    <div class="bg-warning-700 p-4 rounded-lg text-base">
      Can not enter the stage until the submission is accepted.
    </div>
  @else
    <div class="bg-warning-700 p-4 rounded-lg text-base">
      The stage has not yet been opened.
    </div>
  @endif
</div>