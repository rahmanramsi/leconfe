<?php

namespace App\Frontend\ScheduledConference\Pages;

use Squire\Models\Country;
use App\Models\Registration;
use App\Models\Enums\UserRole;
use Livewire\Attributes\Title;
use App\Models\RegistrationType;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Rahmanramsi\LivewirePageGroup\Pages\Page;

class ParticipantRegister extends Page
{
    protected static string $view = 'frontend.scheduledConference.pages.participant-register';

    protected static ?string $slug = 'participant-registration';

    public bool $is_submit = false;

    public array $formData;

    public $type;

    public function mount()
    {
        $isLogged = auth()->check();
        $userRegistration = !$isLogged ? null : Registration::select('*')
            ->whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())
            ->whereUserId(auth()->user()->id)
            ->first();
        if ($userRegistration)
            return redirect(route(ParticipantRegisterStatus::getRouteName()));
    }

    public function rules()
    {
        $rules =  [
            'type' => 'required',
        ];
        return $rules;
    }

    public function messages()
    {
        $message = [
            'type' => 'Registration type have to selected.',
        ];
        return $message;
    }

    public function register()
    {
        if(!auth()->check()) return;

        $data = $this->validate();
        $registration_type = RegistrationType::where('id', $data['type'])->first();

        // validation
        if(!$registration_type) return;
        if($registration_type->isExpired()) return;
        if($registration_type->getQuotaLeft() <= 0) return;
        
        $this->formData = Arr::only($data, 'type');
        $this->is_submit = true;

        return 1;
    }

    public function confirm()
    {
        if(!auth()->check()) return;

        $data = $this->formData;
        $registration_type = RegistrationType::where('id', $data['type'])->first();
        Registration::create([
            'user_id' => auth()->user()->id,
            'registration_type_id' => $data['type'],
            'paid_at' => $registration_type->currency === 'free' ? now() : null,
        ]);

        return redirect(request()->header('Referer'));
    }

    public function cancel()
    {
        $this->formData = [];
        $this->is_submit = false;

        return 1;
    }

    protected function getViewData(): array
    {
        $currentScheduledConference = app()->getCurrentScheduledConference();

        $isLogged = auth()->check();

        $userRegistration = !$isLogged ? null : Registration::select('*')
            ->whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())
            ->whereUserId(auth()->user()->id)
            ->first();

        $userModel = !$isLogged ? null : auth()->user();

        $userCountry = !$isLogged ? null : Country::find(auth()->user()->getMeta('country', null));

        $registrationTypeList = RegistrationType::select('*')
            ->whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())
            ->get();

        $registrationType = null;
        if(isset($this->formData['type'])) {
            $registrationType = RegistrationType::where('id', $this->formData['type'])->first();
        }
            
        return [
            // account registration
            'countries' => Country::all(),
            'roles' => UserRole::selfAssignedRoleNames(),
            'privacyStatementUrl' => '#',
            // participant registration
            'currentScheduledConference' => $currentScheduledConference,
            'isLogged' => $isLogged,
            'userModel' => $userModel,
            'userCountry' => $userCountry,
            'userRegistration' => $userRegistration,
            'registrationTypeList' => $registrationTypeList,
            // participant registration confirm
            'isSubmit' => $this->is_submit,
            'registrationType' => $registrationType,
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => 'Home',
            'Audience Registration',
        ];
    }

    public static function routes(PageGroup $pageGroup): void
    {
        $slug = static::getSlug();
        Route::get("/{$slug}", static::class)
            ->middleware(static::getRouteMiddleware($pageGroup))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($pageGroup))
            ->name((string) str($slug)->replace('/', '.'));
    }
}
