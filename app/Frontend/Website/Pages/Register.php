<?php

namespace App\Frontend\Website\Pages;

use App\Models\Conference;
use Squire\Models\Country;
use Illuminate\Support\Arr;
use App\Models\Enums\UserRole;
use Filament\Facades\Filament;
use App\Actions\User\UserCreateAction;
use App\Facades\Setting;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Contracts\Support\Htmlable;

class Register extends Page
{
    use WithRateLimiting;

    protected static string $view = 'frontend.website.pages.register';

    public $given_name = null;

    public $family_name = null;

    public $affiliation = null;

    public $country = null;

    public $email = null;

    public $password = null;

    public $password_confirmation = null;

    public $privacy_statement_agree = false;

    public $selfAssignRoles = [];

    public $registerComplete = false;

    public function mount()
    {
        if (Filament::auth()->check()) {
            $this->redirect($this->getRedirectUrl(), navigate: false);
        }

        abort_unless(Setting::get('allow_registration'), 403);
    }

    public function getTitle(): string|Htmlable
    {
        return $this->registerComplete ? __('general.registration_complete') : __('general.register');
    }

    public function rules()
    {
        $rules =  [
            'given_name' => [
                'required',
            ],
            'family_name' => [
                'nullable',
            ],
            'affiliation' => [
                'nullable',
            ],
            'country' => [
                'nullable',
            ],
            'email' => [
                'required',
                'email',
                'indisposable',
                'unique:users,email'
            ],
            'password' => [
                'required',
                'confirmed',
                'min:12',
            ],
            'privacy_statement_agree' => [
                'required',
            ],
        ];

        if (app()->getCurrentConference()){
            $rules['selfAssignRoles'] = [
                'required',
            ];
        } else {
            $rules['selfAssignRoles'] = [
                'array',
            ];
        }

        return $rules;
    }

    public function getBreadcrumbs(): array
    {
        return [
            url('/') => __('general.home'),
            $this->getTitle(),
        ];
    }

    public function getRedirectUrl(): string
    {
        return app()->getCurrentConference() ? Filament::getPanel()->getUrl() : route('filament.administration.home');
    }

    protected function getViewData(): array
    {
        $data = [
            'countries' => Country::all(),
            'roles' => UserRole::selfAssignedRoleNames(),
            'privacyStatementUrl' => '#',
            'loginUrl' => app()->getLoginUrl(),
        ];

        return $data;
    }

    public function register()
    {
        try {
            $this->rateLimit(5, 300);
        } catch (TooManyRequestsException $exception) {
            $this->addError('throttle', __('general.throttle_to_many_register_attempts', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => ceil($exception->secondsUntilAvailable / 60),
            ]));

            return null;
        }

        $data = $this->validate();
        $user = UserCreateAction::run([
            ...Arr::only($data, ['given_name', 'family_name', 'email', 'password']),
            'meta' => Arr::only($data, ['affiliation', 'country']),
        ]);

        if (app()->getCurrentConference()){
            $user->assignRole($data['selfAssignRoles']);
        } else {
            foreach ($data['selfAssignRoles'] as $conferenceId => $roles) {
                // get keys of roles where value is true
                $roles = array_keys(array_filter($roles));
                $user->assignRole($roles);
            }
        }

        Filament::auth()->login($user);

        session()->regenerate();

        $this->registerComplete = true;
    }
}
