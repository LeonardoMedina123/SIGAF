<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('partials.navigation', function (ViewInstance $view): void {
            $user = auth()->user();

            if (! $user instanceof User) {
                return;
            }

            $view->with([
                'recentNotifications' => $user->notifications()->latest()->limit(5)->get(),
                'unreadNotificationCount' => $user->unreadNotifications()->count(),
            ]);
        });
    }
}
