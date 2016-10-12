<?php

namespace Layout\Provider;

use Layout\Cache;
use Layout\Event;
use Layout\Config;
use Layout\Profiler;
use Illuminate\Support\ServiceProvider;
use ViewComponents\ViewComponents\Service\Services;

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
             __DIR__.'/../../layout/default.xml' => base_path('resources/layout/default.xml'),
        ], 'layout');
        $this->publishes([
            __DIR__.'/../../config/layout.php' => config_path('layout.php'),
        ], 'config');
        $this->publishes([
            __DIR__.'/../../assets' => public_path(),
        ], 'public');

        Services::resourceManager()
            ->ignoreCss(['bootstrap', 'bootstrap-datepicker'])
            ->ignoreJs(['bootstrap', 'bootstrap-datepicker', 'jquery']);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->registerLayoutBinder();
        $this->registerBladeTemplate();

        $this->app->register('Lavary\Menu\ServiceProvider');
        //$this->app->register('Collective\Html\HtmlServiceProvider');

        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            //$loader->alias('Layout', '\Layout\Facades\Layout');
            $loader->alias('Menu', 'Lavary\Menu\Facade');
            //$loader->alias('HTML', 'Collective\Html\HtmlFacade');
        });

        $this->mergeConfigFrom(__DIR__.'/../../config/layout.php', 'layout');
        $this->addListerForTopMenu();
    }

    public function registerLayoutBinder()
    {   
        $this->app->bind('\Layout\Core\Contracts\Cacheable', function ($app) {
            return $app['layout.cache'];
        });
        $this->app->bind('\Layout\Core\Contracts\EventsDispatcher', function ($app) {
            return $app['layout.event'];
        });
        $this->app->bind('\Layout\Core\Contracts\ConfigResolver', function ($app) {
            return $app['layout.config'];
        });
        $this->app->bind('\Layout\Core\Contracts\Profiler', function ($app) {
            return $app['layout.profile'];
        });

        $this->app->singleton('layout.cache', function ($app) {
            return new Cache($app['cache']);
        });

        $this->app->bind('layout.event', function ($app) {
            return new Event($app['events']);
        });   

        $this->app->bind('layout.config', function ($app) {
            return new Config($app['config']);
        });
        $this->app->bind('layout.profile', function ($app) {
            return new Profiler();
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

        \Blade::extend(function($view, $compiler) {
            $pattern = '/(?<!\w)(\s*)@inc\(\s*(.*)\)/';

            // get paths
            $viewPath = $compiler->getPath();
            $viewFolder = dirname($viewPath) . '/';
            $root = \Config::get('view.paths')[0] . '/';

            // replace all matches
            return preg_replace_callback($pattern, function($matches) use($root, $viewPath, $viewFolder) {
                // get all parameters
                list($match, $whitespace, $param) = $matches;

                // get the relative path parameter
                $param = preg_replace('%[\\(\\)\'""]%', '', $param);

                // resolve the absolute path
                $path = $viewFolder . $param . '.blade.php';
                $path = realpath($path);

                // check it exists
                if( ! $path ) {
                  throw new \ErrorException("Relative @include '$param' not found in template '$viewPath'");
                }

                // if we still have a real, absolute path, convert it to dot syntax, so Blade can compile it
                $expression = str_replace($root, '', $path);
                $expression = str_replace('.blade.php', '', $expression);
                $expression = str_replace('/', '.', $expression);

                // return the new php to the view
                return "$whitespace<?php echo \$__env->make('$expression', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>";

            }, $view);
        });
    }

    /**
     * Register the top menu when the event is triggred.
     *
     * (this is for example purpose)
     */
    public function addListerForTopMenu()
    {
        if ($this->app['config']['layout.add_sample_menu']) {
            $this->app['events']->listen('page.block.html.topmenu.getMenus.before', function ($menu, $block) {
                $menu->add('About', 'about');
                $menu->add('Blog', 'blog');
                $menu->add('Contact Me', 'contact-me');
            });
        }
    }
}
