<?php
namespace App\Providers;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Traits\ForwardsCalls;

/**
* @mixin \Illuminate\Routing\Router
*/
class RouteServiceProvider extends ServiceProvider
{
    use ForwardsCalls;
/**
* The controller namespace for the application.
*
* @var string|null
*/
    protected $namespace;
/**
* Bootstrap any application services.
*
* @return void
*/
    public function boot()
    {
        $this->setRootControllerNamespace();
        $this->loadRoutes();
    }
/**
* Set the root controller namespace for the application.
*
* @return void
*/
    protected function setRootControllerNamespace()
    {
        if (!is_null($this->namespace)) {
            $this->app[UrlGenerator::class]->setRootControllerNamespace($this->namespace);
        }
    }
/**
* Load the cached routes for the application.
*
* @return void
*/
    protected function loadCachedRoutes()
    {
        $this->app->booted(
            function () {
                include $this->app->getCachedRoutesPath();
            }
        );
    }
/**
* Load the application routes.
*
* @return void
*/
    protected function loadRoutes()
    {
        if (method_exists($this, 'map')) {
            $this->app->call([$this, 'map']);
        }
    }
/**
* Pass dynamic methods onto the router instance.
*
* @param  string $method
* @param  array  $parameters
* @return mixed
*/
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo(
            $this->app->make(Router::class),
            $method,
            $parameters
        );
    }
}
