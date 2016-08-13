<?php
/**
 * Created by PhpStorm.
 * User: sharif ahrari
 * Date: 6/29/2016
 * Time: 1:58 AM
 */

namespace Cobonto;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Cobonto\Classes\Assign;
use Cobonto\Classes\CmsBladeCompiler;
use Cobonto\Classes\Settings;
use Cobonto\Commands\ExportCommand;
use Cobonto\Commands\ImportCommand;
use Cobonto\Commands\ModelCommand;

class CmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('assign', function ($app) {
            return new Assign($app['files']);
        });
        $this->registerCommands();
        $this->registerSettings();
     //   $this->registerCompiler();
    }
    public function boot()
    {
        $this->publishes([
            __DIR__.'/copy/config'=>base_path('config'),
            __DIR__.'/copy/resources/views'=>resource_path('views'),
            __DIR__.'/copy/app'=>app_path(),
            __DIR__.'/copy/database'=>database_path(),
        ]);

    }
    protected function registerCommands()
    {
        $commands = ['Import', 'Model', 'Export'];
        foreach ($commands as $command)
        {
            $this->{'register' . $command . 'Command'}();
        }

        $this->commands(
            'cobonto.import',
            'cobonto.model',
            'cobonto.export'
        );
    }
    protected function registerImportCommand()
    {
        $this->app->singleton('cobonto.import', function ($app)
        {
            return new ImportCommand($app['files']);
        });
    }

    protected function registerModelCommand()
    {
        $this->app->singleton('cobonto.model', function ($app)
        {
            return new ModelCommand($app['files']);
        });
    }

    protected function registerExportCommand()
    {
        $this->app->singleton('cobonto.export', function ($app)
        {
            return new ExportCommand($app['files']);
        });
    }
    protected function registerSettings(){
        $this->app->singleton('settings', function ($app)
        {
            return new Settings([],true);
        });
    }

    /**
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     */
    protected function registerCompiler()
    {
        $resolver = app('view.engine.resolver');
        $app = $this->app;
        $app->singleton('blade.compiler', function($app)
        {
            $cache = $app['config']['view.compiled'];
            return new CmsBladeCompiler($app['files'], $cache);
        });

        $resolver->register('blade', function () use ($app) {
            return new CompilerEngine($app['blade.compiler']);
        });
    }
}