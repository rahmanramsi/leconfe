<?php

namespace App\Frontend\Website\Pages;

use App\Models\User;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Locked;
use Rahmanramsi\LivewirePageGroup\PageGroup;

class ResetPasswordConfirmation extends Page
{
    protected static string $view = 'frontend.website.pages.reset-password-confirmation';

    public User $user;

    public string $hash;

    public string $password;

    public string $password_confirmation;

    #[Locked]
    public bool $success = false;

    public function mount(User $user, string $hash, Request $request)
    {
        if (auth()->check()) {
            $this->redirect($this->getRedirectUrl(), navigate: false);
        }

        if (! $request->hasValidSignature()) {
            // Silently abort the request
            abort(403, 'Invalid or expired signature');
        }

        if ($hash !== sha1($user->email.$user->password.$user->getMeta('last_login'))) {
            abort(403, 'Invalid or expired signature');
        }

    }

    public function getTitle(): string|Htmlable
    {
        return __('general.reset_password_confirmation');
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
            __('general.login'),
        ];
    }

    public function rules()
    {
        return [
            'password' => ['required', 'confirmed', Password::min(12)],
            'password_confirmation' => ['required', 'same:password'],
        ];
    }

    public function submit()
    {
        $data = $this->validate();

        $this->user->update([
            'password' => Hash::make($data['password']),
        ]);

        $this->success = true;
    }

    public static function routes(PageGroup $pageGroup): void
    {
        $slug = static::getSlug();

        Route::get('reset-password-confirmation/{user:email}/{hash}', static::class)
            ->middleware(static::getRouteMiddleware($pageGroup))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($pageGroup))
            ->name((string) str($slug)->replace('/', '.'));
    }
}
