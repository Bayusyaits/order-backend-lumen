<?php

namespace Modules\OrderModule\Providers;

use Modules\OrderModule\Entities\OrderItemEntity;
use Modules\OrderModule\Observers\OrderItemModuleObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class OrderModuleServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerFactories();
        $this->loadMigrationsFrom(module_path('OrderModule', 'Database/Migrations'));
        OrderItemEntity::observe(OrderItemModuleObserver::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path('OrderModule', 'Config/config.php') => config_path('ordermodule.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path('OrderModule', 'Config/config.php'),
            'ordermodule'
        );
    }
    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/ordermodule');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'ordermodule');
        } else {
            $this->loadTranslationsFrom(module_path('OrderModule', 'Resources/lang'), 'ordermodule');
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(module_path('OrderModule', 'Database/factories'));
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
