<?php

namespace App\Http\Middleware;

use App\Facades\Setting;
use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = session('locale');
        $supportedLocales = Setting::get('languages', ['en']);
        
        if ($locale && in_array($locale, $supportedLocales)) {
            App::setLocale($locale);
        } else {
            App::setLocale(Setting::get('default_language', 'en'));
        }

        return $next($request);
    }
}