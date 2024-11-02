<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Frontend\Website\Pages\Page;
use App\Models\Enums\RegistrationPaymentState;
use App\Models\Registration;
use App\Models\RegistrationType;
use App\Models\Timeline;
use App\Models\User;
use App\Notifications\NewRegistration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Squire\Models\Country;

class ParticipantRegister extends Page
{
    protected static string $view = 'frontend.scheduledConference.pages.participant-register';

    protected static ?string $slug = 'participant-registration';

    public bool $isSubmit = false;

    public array $formData;

    public $type;

    public function mount()
    {
        $isLogged = auth()->check();
        $userRegistration = ! $isLogged ? null : Registration::withTrashed()
            ->where('user_id', auth()->user()->id)
            ->first();
        if ($userRegistration) {
            return redirect(route(ParticipantRegisterStatus::getRouteName()));
        }
    }

    public function rules(): array
    {
        $rules = [
            'type' => 'required',
        ];

        return $rules;
    }

    public function messages(): array
    {
        $message = [
            'type' => __('general.registration_type_have_to_selected'),
        ];

        return $message;
    }

    public function register()
    {
        if (! auth()->check()) {
            return;
        }

        $data = $this->validate();
        $registrationType = RegistrationType::where('id', $data['type'])->first();

        if (! $registrationType) {
            return;
        }

        if (! $registrationType->isOpen()) {
            return;
        }

        $this->formData = Arr::only($data, 'type');
        $this->isSubmit = true;

        return 1;
    }

    public function confirm()
    {
        if (! auth()->check()) {
            return;
        }

        $data = $this->formData;
        $registrationType = RegistrationType::where('id', $data['type'])->first();
        $isFree = $registrationType->currency === 'free';

        try {
            $registration = Registration::create([
                'user_id' => auth()->user()->id,
                'registration_type_id' => $data['type'],
            ]);

            $registration->registrationPayment()->create([
                'name' => $registrationType->type,
                'level' => $registrationType->level,
                'description' => $registrationType->getMeta('description'),
                'cost' => $registrationType->cost,
                'currency' => $registrationType->currency,
                'state' => $isFree ? RegistrationPaymentState::Paid : RegistrationPaymentState::Unpaid,
                'paid_at' => $isFree ? now() : null,
            ]);

            User::whereHas('roles', function ($query) {
                $query->whereHas('permissions', function ($query) {
                    $query->where('name', 'Registration:notified');
                });
            })->get()->each(function ($user) use ($registration) {
                $user->notify(
                    new NewRegistration(
                        registration: $registration,
                    )
                );
            });
        } catch (\Throwable $th) {
            throw $th;
        }

        return redirect(request()->header('Referer'));
    }

    public function cancel()
    {
        $this->formData = [];
        $this->isSubmit = false;

        return 1;
    }

    protected function getViewData(): array
    {
        $currentScheduledConference = app()->getCurrentScheduledConference();

        $isLogged = auth()->check();

        $userRegistration = ! $isLogged ? null : Registration::withTrashed()
            ->where('user_id', auth()->user()->id)
            ->first();

        $userModel = ! $isLogged ? null : auth()->user();

        $userCountry = ! $isLogged ? null : Country::find(auth()->user()->getMeta('country', null));

        $registrationTypeList = RegistrationType::select('*')->get();

        $registrationType = null;
        if (isset($this->formData['type'])) {
            $registrationType = RegistrationType::where('id', $this->formData['type'])->first();
        }

        return [
            // participant registration
            'currentScheduledConference' => $currentScheduledConference,
            'isLogged' => $isLogged,
            'userModel' => $userModel,
            'userCountry' => $userCountry,
            'userRegistration' => $userRegistration,
            'registrationTypeList' => $registrationTypeList,
            'registrationOpen' => Timeline::isRegistrationOpen(),
            // participant registration confirm
            'isSubmit' => $this->isSubmit,
            'registrationType' => $registrationType,
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => __('general.home'),
            __('general.participant_registration'),
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
