<?php

namespace App\Http\Middleware;

use App\Classes\Setting;
use App\Facades\Plugin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThemeActivator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!app()->isInstalled()) return $next($request);

        // Do not load theme if API request or App is running in console
        if ($request->expectsJson() || app()->runningInConsole()) {
            return $next($request);
        }

        if($currentScheduledConference = app()->getCurrentScheduledConference()){
            Plugin::getPlugin($currentScheduledConference->getMeta('theme'))?->activate();
            return $next($request);
        }

        if($currentConference = app()->getCurrentConference()){
            Plugin::getPlugin($currentConference->getMeta('theme'))?->activate();
            return $next($request);
        }

        $site = app()->getSite();
        Plugin::getPlugin($site->getMeta('theme'))?->activate();
        return $next($request);
    }
}
