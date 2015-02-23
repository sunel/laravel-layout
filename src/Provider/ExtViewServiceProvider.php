<?php namespace Ext\Provider;

use Illuminate\Support\ServiceProvider;
use Ext\Factory;
use Ext\Layout;
use Ext\Layout\Update;

class ExtViewServiceProvider extends ServiceProvider
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
        ]);
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
