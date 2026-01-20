<?php

namespace App\Http\Middleware;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use mysql_xdevapi\Exception;

class SetLang
{

    public function handle($request, Closure $next)
    {
        try {
            $defaultLang = \App\Models\Language::where('default', 1)->first();
        } catch (\Exception $exception) {

        }

        if (session()->has('lang')) {
            $current_lang = \App\Models\Language::where('slug', session()->get('lang'))->first();
            if (!empty($current_lang)) {
                Carbon::setLocale($current_lang?->slug);
                app()->setLocale($current_lang?->slug);
            } else {
                session()->forget('lang');
            }
        } else {
            try {
                app()->setLocale($defaultLang?->slug ?? 'en');
                Carbon::setLocale($defaultLang?->slug ?? 'en');
            } catch (\Exception $exception) {

            }
        }
        return $next($request);
    }
}
