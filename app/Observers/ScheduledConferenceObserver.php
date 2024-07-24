<?php

namespace App\Observers;

use App\Actions\Committees\CommitteeRolePopulateDefaultDataAction;
use App\Actions\Speakers\SpeakerRolePopulateDefaultDataAction;
use App\Actions\SubmissionFiles\FilesTypePopulateAction;
use App\Models\NavigationMenu;
use App\Models\NavigationMenuItem;
use App\Models\Review;
use App\Models\ScheduledConference;
use HTML5;
use Illuminate\Support\HtmlString;

class ScheduledConferenceObserver
{
    /**
     * Handle events after all transactions are committed.
     *
     * @var bool
     */
    public $afterCommit = true;

    /**
     * Handle the Conference "created" event.
     */
    public function created(ScheduledConference $scheduledConference): void
    {
        CommitteeRolePopulateDefaultDataAction::run($scheduledConference);
        SpeakerRolePopulateDefaultDataAction::run($scheduledConference);

        $primaryNavigationMenu = NavigationMenu::create([
            'name' => 'Primary Navigation Menu',
            'handle' => 'primary-navigation-menu',
            'conference_id' => $scheduledConference->conference_id,
            'scheduled_conference_id' => $scheduledConference->getKey(),
        ]);

        $userNavigationMenu = NavigationMenu::create([
            'name' => 'User Navigation Menu',
            'handle' => 'user-navigation-menu',
            'conference_id' => $scheduledConference->conference_id,
            'scheduled_conference_id' => $scheduledConference->getKey(),
        ]);

        NavigationMenuItem::insert([
            [
                'navigation_menu_id' => $primaryNavigationMenu->getKey(),
                'label' => 'Home',
                'type' => 'home',
                'order_column' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'navigation_menu_id' => $primaryNavigationMenu->getKey(),
                'label' => 'About',
                'type' => 'about',
                'order_column' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'navigation_menu_id' => $primaryNavigationMenu->getKey(),
                'label' => 'Announcements',
                'type' => 'announcements',
                'order_column' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'navigation_menu_id' => $userNavigationMenu->getKey(),
                'label' => 'Login',
                'type' => 'login',
                'order_column' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'navigation_menu_id' => $userNavigationMenu->getKey(),
                'label' => 'Register',
                'type' => 'register',
                'order_column' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $usernameNavigation = NavigationMenuItem::create([
            'navigation_menu_id' => $userNavigationMenu->getKey(),
            'label' => '{$username}',
            'type' => 'dashboard',
            'order_column' => 3,
        ]);

        NavigationMenuItem::insert([
            [
                'navigation_menu_id' => $userNavigationMenu->getKey(),
                'parent_id' => $usernameNavigation->getKey(),
                'label' => 'Dashboard',
                'type' => 'dashboard',
                'order_column' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'navigation_menu_id' => $userNavigationMenu->getKey(),
                'parent_id' => $usernameNavigation->getKey(),
                'label' => 'Profile',
                'type' => 'profile',
                'order_column' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'navigation_menu_id' => $userNavigationMenu->getKey(),
                'parent_id' => $usernameNavigation->getKey(),
                'label' => 'Logout',
                'type' => 'logout',
                'order_column' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        FilesTypePopulateAction::run($scheduledConference);
        
        $scheduledConference->setManyMeta([
            'before_you_begin' =>  <<<HTML
            <p>Thank you for submitting to the $scheduledConference->title. You will be asked to upload files, identify co-authors, and provide information such as the title and abstract.</p>
            <p>Please read our Submission Guidelines if you have not done so already. When filling out the forms, provide as many details as possible in order to help our editors evaluate your work.</p>
            <p>Once you begin, you can save your submission and come back to it later. You will be able to review and correct any information before you submit.</p>
        HTML,
            'submission_checklist' => <<<HTML
            <p>All submissions must meet the following requirements.</p>
            <ul>
                <li>The submission has not been previously published, nor is it before another journal for consideration (or an explanation has been provided in Comments to the Editor).</li>
                <li>The submission file is in OpenOffice, Microsoft Word, or RTF document file format.</li>
                <li>Where available, URLs for the references have been provided.</li>
                <li>The text is single-spaced; uses a 12-point font; employs italics, rather than underlining (except with URL addresses); and all illustrations, figures, and tables are placed within the text at the appropriate points, rather than at the end.</li>
                <li>The text adheres to the stylistic and bibliographic requirements outlined in the Author Guidelines.</li>
            </ul>
        HTML,
            'review_mode' => Review::MODE_DOUBLE_ANONYMOUS,
            'review_invitation_response_deadline' => 30,
            'review_completion_deadline' => 30,
        ]);
    }
}
