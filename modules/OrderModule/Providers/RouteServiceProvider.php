<?php

namespace Modules\OrderModule\Providers;

use App\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\OrderModule\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const MODULE = 'OrderModule';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
        //
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'middleware' => 'throttle:300,1' // limit 300/ minute/ user
        ], function ($routerThrottle) {
            $routerThrottle->group([
                'prefix'    => 'v'. substr(env('APP_VERSION', 1), 0, 1),
                'middleware' => 'cors'
            ], function ($routeVersion) {
                $routeVersion->group([
                    'prefix'    => 'order',
                    'namespace' => $this->moduleNamespace,
                ], function ($router) {
                    require module_path(self::MODULE, 'Routes/api.php');
                });
            });
        });
    }
}
