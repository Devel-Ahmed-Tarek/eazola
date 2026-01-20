<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // لا داعي لوضع أي شيء هنا للـ macros
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $flash_messages = ['success', 'warning', 'info', 'danger', 'primary'];

        foreach ($flash_messages as $flmsg) {
            Response::macro($flmsg, function ($text) use ($flmsg) {
                return back()->with([
                    'msg' => $text,
                    'type' => $flmsg
                ]);
            });
        }
    }
}
