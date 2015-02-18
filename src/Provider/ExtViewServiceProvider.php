<?php namespace Ext\Provider;

use Illuminate\Support\ServiceProvider;
use Ext\Factory;
use Ext\Layout;
use Ext\Layout\Update;

class ExtViewServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->loadViewsFrom(__DIR__.'/../../views/template', 'render');
		$this->publishes([
		     __DIR__.'/../../views' => base_path('resources/views/vendor/render'),
		]);
		
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerFactory();
		$this->registerTemplatLayout();
		$this->registerBladeTemplat();


        $this->app->booting(function()
        {
            //$loader = \Illuminate\Foundation\AliasLoader::getInstance();
            //$loader->alias('StringView', 'sngrl\StringBladeCompiler\Facades\StringView');
        });
	}
	
	/**
	 * Register the view environment.
	 *
	 * @return void
	 */
	public function registerFactory()
	{
		$this->app->singleton('render', function($app)
		{
			// Next we need to grab the engine resolver instance that will be used by the
			// environment. The resolver will be used by an environment to get each of
			// the various engine implementations such as plain PHP or Blade engine.
			$resolver = $app['view.engine.resolver'];
			$finder = $app['view.finder'];
			$env = new Factory($resolver, $finder, $app['events']);
			// We will also set the container instance on this view environment since the
			// view composers may be classes registered in the container, which allows
			// for great testable, flexible composers for the application developer.
			$env->setContainer($app);
			$env->share('app', $app);
			return $env;
		});
	}

	public function registerTemplatLayout()
	{	
		$this->app->singleton('render.layout', function($app)
		{
				return new Layout($app);
		});
		
		$this->app->bind('render.layout.update', function($app)
		{
				return new Update();
		});
	}

	/**
	 * Register the view environment.
	 *
	 * @return void
	 */
	public function registerBladeTemplat()
	{
		\Blade::extend(function($view, $compiler)
		{
		   return preg_replace('/\{\?(.+)\?\}/', '<?php ${1} ?>', $view);;
		});
	}

}
