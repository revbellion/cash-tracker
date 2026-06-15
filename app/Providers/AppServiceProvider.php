<?php

namespace App\Providers;

use App\Models\Receivable;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        if (app()->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        View::composer('layouts.app', function ($view) {
            $view->with('unpaidPiutangCount', Receivable::unpaid()->count());
        });
    }
}
