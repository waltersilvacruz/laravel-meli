<?php

namespace WebDEV\Meli;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use WebDEV\Meli\Databases\Repositories\MeliAppTokenRepository;

class MeliServiceProvider extends BaseServiceProvider {
    /**
     * Register services.
     *
     * @return void
     */
    public function register() {
        $this->mergeConfigFrom(__DIR__ . '/../config/meli.php', 'meli');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot() {
        $this->registerPublishes();
        $this->registerRoutes();
        $this->registerViews();
    }

    /**
     * Register publishes
     */
    private function registerPublishes() {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->publishes([
                __DIR__ . '/../config/meli.php' => config_path('meli.php'),
            ], 'meli-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'meli-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => base_path('resources/views/vendor/meli'),
            ], 'meli-views');
        }
    }

    /**
     * Register routes
     */
    private function registerRoutes() {
        $config = $this->app->config->get('meli.route');
        $this->app->router->prefix($config['prefix'])
            ->as('meli.')
            ->namespace('WebDEV\Meli\Http\Controllers')
            ->group(function (Router $router) use ($config) {
                if ($config['middleware']['authenticated']) {
                    $router->get($config['paths']['connect'], 'MeliController@connect')
                        ->middleware($config['middleware']['authenticated'])
                        ->name('connect');

                    $router->delete($config['paths']['disconnect'], 'MeliController@disconnect')
                        ->middleware($config['middleware']['authenticated'])
                        ->name('disconnect');

                } else {
                    $router->get($config['paths']['connect'], 'MeliController@connect')
                        ->name('connect');

                    $router->get($config['paths']['disconnect'], 'MeliController@disconnect')
                        ->name('disconnect');
                }

                $router->get($config['paths']['token'], 'MeliController@token')
                    ->name('token');
            });
    }

    /**
     * Register views
     */
    private function registerViews() {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'meli');
    }
}
