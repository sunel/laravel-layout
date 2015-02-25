<?php namespace Layout\Provider;

use Layout\Factory;
use Layout\Layout;
use Layout\Layout\Update;
use Illuminate\Support\ServiceProvider;

class LayoutServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../views/template', 'render');
        $this->loadViewsFrom(__DIR__.'/../../views', 'page');
        $this->publishes([
             __DIR__.'/../../views' => base_path('resources/views/vendor/render'),
        ],'view');
        $this->publishes([
            __DIR__ . '/../../config/layout.php' => config_path('layout.php')
        ], 'config');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->registerFactory();
        $this->registerTemplatLayout();
        $this->registerBladeTemplat();

        $this->app->booting(function () {
            //$loader = \Illuminate\Foundation\AliasLoader::getInstance();
            //$loader->alias('StringView', 'sngrl\StringBladeCompiler\Facades\StringView');
        });

        $this->mergeConfigFrom(__DIR__ . '/../../config/layout.php', 'layout');
    }

    /**
     * Register the view environment.
     */
    public function registerFactory()
    {
        $this->app->singleton('render', function ($app) {
            $env = new Factory($app['events']);

            return $env;
        });
    }

    public function registerTemplatLayout()
    {
        $this->app->singleton('render.layout', function ($app) {
                return new Layout($app);
        });

        $this->app->bind('render.layout.update', function ($app) {
                return new Update();
        });
    }

    /**
     * Register the view environment.
     */
    public function registerBladeTemplat()
    {
        \Blade::extend(function ($view, $compiler) {
           return preg_replace('/\{\?(.+)\?\}/', '<?php ${1} ?>', $view);
        });
    }
}
