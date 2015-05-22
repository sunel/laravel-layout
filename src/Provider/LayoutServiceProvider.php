<?php

namespace Layout\Provider;

use Illuminate\Support\ServiceProvider;
use Layout\Factory;
use Layout\Layout;
use Layout\Layout\Update;

class LayoutServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../views', 'render');
        $this->publishes([
             __DIR__.'/../../views' => base_path('resources/views/vendor/layout'),
        ], 'view');
        $this->publishes([
             __DIR__.'/../../layout' => base_path('resources/layout/vendor/layout'),
        ], 'layout');
        $this->publishes([
            __DIR__.'/../../config/layout.php' => config_path('layout.php'),
        ], 'config');
        $this->publishes([
            __DIR__.'/../../assets' => public_path(),
        ], 'public');
		
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->registerFactory();
        $this->registerTemplatLayout();
        $this->registerBladeTemplate();

        $this->app->register('Layout\Provider\LavaryMenuServiceProvider');
        $this->app->register('Collective\Html\HtmlServiceProvider');

        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            //$loader->alias('Layout', '\Layout\Facades\Layout');
            $loader->alias('Menu', 'Lavary\Menu\Facade');
            $loader->alias('HTML', 'Collective\Html\HtmlFacade');
        });

        $this->mergeConfigFrom(__DIR__.'/../../config/layout.php', 'layout');
		$this->addListerForTopMenu();
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
    public function registerBladeTemplate()
    {
        \Blade::extend(function ($view, $compiler) {
           return preg_replace('/\{\?(.+)\?\}/', '<?php ${1} ?>', $view);
        });
    }
	
	/**
     * Register the top menu when the event is triggred.
	 * 
	 * (this is for example purpose)
	 * 
     */
	public function addListerForTopMenu()
	{
		if ($this->app['config']['layout.add_sample_menu']) {
			$this->app['events']->listen('page.block.html.topmenu.getMenus.before',function($menu,$block){
		 		$menu->add('About', 'about');
		        $menu->add('Blog', 'blog');
		        $menu->add('Contact Me', 'contact-me');
			});
		}
	}
}
