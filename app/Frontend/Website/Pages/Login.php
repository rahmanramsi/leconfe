<?php

namespace App\Frontend\Website\Pages;

use Livewire\Attributes\Rule;
use Filament\Facades\Filament;
use Livewire\Attributes\Title;
use Rahmanramsi\LivewirePageGroup\Pages\Page;
use Illuminate\Validation\ValidationException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Illuminate\Contracts\Support\Htmlable;

class Login extends Page
{
    use WithRateLimiting;

    protected static string $view = 'frontend.website.pages.login';

    #[Rule('required|email')]
    public ?string $email = null;

    #[Rule('required')]
    public ?string $password = null;

    #[Rule('boolean')]
    public bool $remember = false;

    public function mount()
    {
        if (auth()->check()) {
            $this->redirect($this->getRedirectUrl(), navigate: false);
        }
    }

    public function getTitle(): string|Htmlable
    {
        return __('general.login');
    }

    public function getRedirectUrl(): string
    {
        return route('filament.administration.home');
    }

    public function getViewData() : array
    {
        return [
            'registerUrl' => null,
            'resetPasswordUrl' => route('livewirePageGroup.website.pages.reset-password'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            url('/') => __('general.home'),
            __('general.login'),
        ];
    }

    public function login()
    {
        try {
            $this->rateLimit(5, 300);
        } catch (TooManyRequestsException $exception) {
            $this->addError('throttle', __('general.throttle_to_many_login_attempts', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => ceil($exception->secondsUntilAvailable / 60),
            ]));

            return null;
        }

        $this->validate();

        if (! auth()->attempt([
            'email' => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => __('general.failed_credentials'),
            ]);
        }

        session()->regenerate();

        auth()->user()->setMeta('last_login', now());

        $this->redirect($this->getRedirectUrl(), navigate: false);
    }
}
