<?php namespace Laratash;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\ViewFinderInterface;

class LaratashServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->setupConfig();

        $this->registerMustacheEngine();

        $this->registerMustacheViewExtension();
    }

    /**
     * Register the Blade engine implementation.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerBladeEngine($resolver)
    {
        $app = $this->app;
        // The Compiler engine requires an instance of the CompilerInterface, which in
        // this case will be the Blade compiler, so we'll first create the compiler
        // instance to pass into the engine so it can compile the views properly.
        $app->singleton('blade.compiler', function($app)
        {
            $cache = $app['config']['view.compiled'];
            return new BladeCompiler($app['files'], $cache);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laratash', 'mustache.engine'];
    }

    private function setupConfig()
    {
        $config = __DIR__ . '/config/config.php';
        $this->mergeConfigFrom($config, 'laratash');
    }

    private function registerMustacheEngine()
    {
        $this->app->bind('mustache.engine', function() {
            return $this->app->make('Laratash\MustacheEngine');
        });

        $this->app->extend('view.engine.resolver', function($resolver, $app) {
            $this->registerBladeEngine($resolver);
            return $resolver;
        });
    }

    private function registerMustacheViewExtension()
    {
        $this->app['view']->addExtension(
            'mustache',
            'mustache',
            function () {
                return $this->app['mustache.engine'];
            }
        );
    }
}
