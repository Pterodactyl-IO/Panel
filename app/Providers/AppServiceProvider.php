<?php

namespace Pterodactyl\Providers;

use View;
use Cache;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Subuser;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Pterodactyl\Observers\UserObserver;
use Pterodactyl\Observers\ServerObserver;
use Pterodactyl\Observers\SubuserObserver;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use DaneEveritt\LoginNotifications\NotificationServiceProvider;
use Barryvdh\Debugbar\ServiceProvider as DebugbarServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        User::observe(UserObserver::class);
        Server::observe(ServerObserver::class);
        Subuser::observe(SubuserObserver::class);

        View::share('appVersion', $this->versionData()['version'] ?? 'undefined');
        View::share('appIsGit', $this->versionData()['is_git'] ?? false);
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Laravel\Dusk\DuskServiceProvider::class);
            $this->app->register(DebugbarServiceProvider::class);
            $this->app->register(IdeHelperServiceProvider::class);
        }

        if (config('pterodactyl.auth.notifications')) {
            $this->app->register(NotificationServiceProvider::class);
        }
    }

    /**
     * Return version information for the footer.
     *
     * @return array
     */
    protected function versionData()
    {
        return Cache::remember('git-version', 5, function () {
            if (file_exists(base_path('.git/HEAD'))) {
                $head = explode(' ', file_get_contents(base_path('.git/HEAD')));

                if (array_key_exists(1, $head)) {
                    $path = base_path('.git/' . trim($head[1]));
                }
            }

            if (isset($path) && file_exists($path)) {
                return [
                    'version' => substr(file_get_contents($path), 0, 8),
                    'is_git' => true,
                ];
            }

            return [
                'version' => config('app.version'),
                'is_git' => false,
            ];
        });
    }
}
