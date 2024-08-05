<?php

namespace App\Http\Middleware;

use App\Facades\Setting;
use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $sessionLocale = session('locale');
        $supportedLocales = Setting::get('languages', ['en']);
        
        $locale = $sessionLocale && in_array($sessionLocale, $supportedLocales) ? $sessionLocale : Setting::get('default_language', 'en');

        App::setLocale($locale);

        return $next($request);
    }
}