<?php

namespace Fxiao\LumenTools;

use Illuminate\Support\ServiceProvider;

class LumenToolsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (env('APP_DEBUG', false) && env('DEV_HELPERS', false)) {
            $this->registerRoutes();
        }
    }

    public function registerRoutes()
    {
        $api = app('Dingo\Api\Routing\Router');

        $api->version('v1', [], function ($api) {
            $api->get('/dev-helpers', [
                'as' => 'helpers.index',
                'uses' => '\Fxiao\LumenTools\HelpersController@index',
            ]);

            $api->post('/dev-helpers', [
                'as' => 'helpers.store',
                'uses' => '\Fxiao\LumenTools\HelpersController@store',
            ]);

        });
        
    }
}
