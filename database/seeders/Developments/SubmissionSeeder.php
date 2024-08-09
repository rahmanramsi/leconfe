<?php

namespace Database\Seeders\Developments;

use App\Models\Enums\UserRole;
use App\Models\ScheduledConference;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ScheduledConference::query()
            ->with(['conference'])
            ->lazy()
            ->each(function (ScheduledConference $scheduledConference) {
                $users = User::query()
                    ->withoutGlobalScopes()
                    ->whereHas('roles', fn ($query) => $query
                        ->withoutGlobalScopes()
                        ->where('roles.conference_id', $scheduledConference->conference_id)
                        ->where('name', UserRole::Author))
                    ->limit(10)
                    ->get();

                foreach ($users as $user) {
                    Submission::factory()
                        ->count(1)
                        ->for($scheduledConference->conference)
                        ->for($user)
                        ->for($scheduledConference)
                        ->create();
                }
            });
    }
}
