@php
    use App\Models\Enums\SubmissionStage; 
    use App\Models\Enums\SubmissionStatus;
    use App\Panel\Livewire\Submissions\Components;
    use App\Models\Enums\UserRole;
@endphp
<div class="space-y-6">
    @if ($stageOpened && $submission->stage != SubmissionStage::CallforAbstract)
        <div class="grid grid-cols-12 gap-4">
            <div class="space-y-4 col-span-8">
                {{-- Papers --}}
                @livewire(Components\Files\PaperFiles::class, ['submission' => $submission])

                {{-- Reviewer List --}}
                @livewire(Components\ReviewerList::class, ['record' => $submission])

                {{-- Revision Files --}}
                @livewire(Components\Files\RevisionFiles::class, ['submission' => $submission])

                {{-- Discussions --}}
                {{-- @livewire(App\Panel\Livewire\Submissions\SubmissionDetail\Discussions::class, ['record' => $submission]) --}}
            </div>
            <div class="self-start sticky top-24 flex flex-col gap-4 col-span-4">
                @if ($submission->revision_required)
                    <div class="flex items-center p-4 text-sm rounded-lg  border border-warning-400 bg-warning-200 text-warning-600"
                        role="alert">
                        <span class="text-base text-center">
                            Revisions have been requested.
                        </span>
                    </div>
                @endif

                {{-- Participants --}}
                @livewire(Components\ParticipantList::class, ['submission' => $submission, 'lazy' => true])

                        @if ($submission->stage == SubmissionStage::PeerReview && $submission->status == SubmissionStatus::OnReview)
                            @can('skipReview', $submission)
                                {{ $this->skipReviewAction() }}
                            @endcan
                            @can('requestRevision', $submission)
                                {{ $this->requestRevisionAction() }}
                            @endcan
                            @can('acceptPaper', $submission)
                                {{ $this->acceptSubmissionAction() }}
                            @endcan
                            @can('declinePaper', $submission)
                                {{ $this->declineSubmissionAction() }}
                            @endcan
                        @endif
            </div>
        </div>
        <x-filament-actions::modals />
    @elseif($submission->stage == App\Models\Enums\SubmissionStage::CallforAbstract)
        <div class="bg-warning-500 text-white dark:bg-warning-700 p-4 rounded-lg text-base">
            Can not enter the stage until the submission is accepted.
        </div>
    @else
        <div class="bg-warning-500 text-white dark:bg-warning-700 p-4 rounded-lg text-base">
            The stage has not yet been opened.
        </div>
    @endif
</div>
