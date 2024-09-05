@use('App\Panel\ScheduledConference\Livewire\Submissions\Components')
@use('App\Models\Enums\SubmissionStage')
@use('App\Constants\SubmissionFileCategory')
@use('App\Models\Enums\SubmissionStatus')
@use('App\Models\Enums\UserRole')

@php
    $user = auth()->user();
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-8 space-y-4">
            @livewire(Components\Files\AbstractFiles::class, ['submission' => $submission, 'category' => SubmissionFileCategory::SUPPLEMENTARY_FILES])

            @livewire(Components\Discussions\DiscussionTopic::class, ['submission' => $submission, 'stage' => SubmissionStage::CallforAbstract, 'lazy' => true])
        </div>
        <div class="flex flex-col self-start col-span-4 gap-3" x-data="{ decision:@js($submissionDecision) }">
            @if($submission->getEditors()->isEmpty() && ! $user->hasAnyRole([UserRole::ScheduledConferenceEditor, UserRole::TrackEditor]))
                <div class="px-4 py-3.5 text-base text-white rounded-lg border-2 border-primary-700 bg-primary-500">
                    {{ $user->can('assignParticipant', $submission) ? __('general.assign_an_editor_to_enable_the_editorial') : __('general.no_editor_assigned_submission') }}
                </div>
            @else

                @if($submissionDecision)
                    <div class="px-6 py-5 space-y-3 overflow-hidden bg-white shadow-sm rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10">
                        <div class="text-base">
                            {{ $submission->status == SubmissionStatus::Declined ? __('general.submission_declined') : __('general.submission_accepted_for_review') }}
                        </div>
                        <button class="text-sm underline text-primary-500"
                            @@click="decision = !decision" x-text="decision ? 'Change Decision' : 'Cancel'"
                        ></button>
                    </div>
                @endif

                <div @class([
                    'space-y-4',
                    'hidden' => !($user->hasAnyRole([UserRole::ConferenceManager, UserRole::Admin]) || $this->submission->isParticipantEditor($user)) || in_array($submission->status, [SubmissionStatus::Published])
                ]) x-show="!decision">
                    @if ($user->can('acceptAbstract', $submission) && ! in_array($this->submission->status, [SubmissionStatus::OnPayment, SubmissionStatus::OnReview, SubmissionStatus::PaymentDeclined, SubmissionStatus::Editing, SubmissionStatus::OnPresentation]))
                        {{ $this->acceptAction() }}
                    @endif
                    @if ($user->can('declineAbstract', $submission) && ! in_array($this->submission->status, [SubmissionStatus::Declined]))
                        {{ $this->declineAction() }}
                    @endif
                </div>
            @endif

            @livewire(Components\ParticipantList::class, ['submission' => $submission])
        </div>
    </div>
    <x-filament-actions::modals />
</div>
