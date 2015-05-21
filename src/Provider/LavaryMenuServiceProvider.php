<?php namespace Layout\Provider;

use Layout\Lavary\Menu;
use Lavary\Menu\ServiceProvider;

class LavaryMenuServiceProvider extends ServiceProvider {

	
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;
	
	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot() {
		
		$this->publishes([
			__DIR__.'/../../config/menu.php' => config_path('laravel-menu.php'),
		]);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {
		
		$app = $this->app;
		$this->registerBladeTemplate();
		// merge default configs
		$this->mergeConfigFrom(__DIR__.'/../../config/menu.php', 'laravel-menu');

		$app['menu'] = $app->share(function ($app) {
            return new Menu($app['config']->get('laravel-menu'));
        });
	}
	
	/**
     * Register the view environment.
     */
    public function registerBladeTemplate()
    {
        /*
		|--------------------------------------------------------------------------
		| @lm-attrs
		|--------------------------------------------------------------------------
		|
		| Buffers the output if there's any.	
		| The output will be passed to mergeStatic()
		| where it is merged with item's attributes
		|
		*/
		\Blade::extend(function($view, $compiler)
		{
			$pattern = '/(\s*)@lm-attrs\s*\((\$[^)]+)\)/';
			return preg_replace($pattern, 
				'$1<?php $lm_attrs = $2->attr(); ob_start(); ?>',
				$view);
		});
		/*
		|--------------------------------------------------------------------------
		| @lm-endattrs
		|--------------------------------------------------------------------------
		|
		| Reads the buffer data using ob_get_clean()
		| and passes it to MergeStatic(). 
		| mergeStatic() takes the static string,
		| converts it into a normal array and merges it with others.
		| 
		*/
		\Blade::extend(function($view, $compiler)
		{
			$pattern = $compiler->CreatePlainMatcher('lm-endattrs');
			return preg_replace($pattern, 
				'$1<?php echo \Lavary\Menu\Builder::mergeStatic(ob_get_clean(), $lm_attrs); ?>$2', 
				$view);
		});
    }

}
