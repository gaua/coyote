<?php

namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;
use Coyote\Services\Session\Viewers;
use Coyote\Services\Session\Handler;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->session->extend('coyote', function ($app) {
            $connectionName     = $app['config']->get('session.connection');
            $databaseConnection = $app['db']->connection($connectionName);
            $lifetime = $app['config']->get('session.lifetime');

            $table = $databaseConnection->getTablePrefix() . $app['config']->get('session.table');

            return new Handler($databaseConnection, $table, $lifetime, $app);
        });

        $this->app->bind('session.viewers', function ($app) {
            return new Viewers(
                $app['Coyote\Repositories\Eloquent\SessionRepository'],
                $app['Illuminate\Http\Request']
            );
        });
    }
}
