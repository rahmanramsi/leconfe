<?php

namespace App\Frontend\Website\Pages;

use App\Mail\Templates\ResetPasswordMail;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Locked;

class ResetPassword extends Page
{
    use WithRateLimiting;

    protected static string $view = 'frontend.website.pages.reset-password';

    public ?string $email = null;

    #[Locked]
    public bool $success = false;

    public function mount()
    {
        if (auth()->check()) {
            $this->redirect($this->getRedirectUrl(), navigate: false);
        }
    }

    public function getTitle(): string|Htmlable
    {
        return __('general.reset_password');
    }

    public function getRedirectUrl(): string
    {
        return route('filament.administration.home');
    }

    public function getViewData(): array
    {
        return [
            'registerUrl' => null,
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            url('/') => __('general.home'),
            __('general.reset_password'),
        ];
    }

    public function rules()
    {
        return [
            'email' => 'required|email|exists:users',
        ];
    }

    public function submit()
    {
        try {
            $this->rateLimit(5, 300, 'submit');
        } catch (TooManyRequestsException $exception) {
            $this->addError('throttle', __('general.throttle_to_many_reset_password_attempts', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => ceil($exception->secondsUntilAvailable / 60),
            ]));

            return null;
        }

        $this->validate();

        $user = User::where('email', $this->email)->first();

        Mail::to($this->email)
            ->send(new ResetPasswordMail($user));

        $this->success = true;
    }
}
