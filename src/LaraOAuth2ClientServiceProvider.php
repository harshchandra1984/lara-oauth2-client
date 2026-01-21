<?php

namespace Larawizards\LaraOAuth2Client;

use Larawizards\LaraOAuth2Client\Console\InstallCommand;
use Larawizards\LaraOAuth2Client\Http\Middleware\OAuth2Authenticate;
use Larawizards\LaraOAuth2Client\Services\UserService;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LaraOAuth2ClientServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/lara-oauth2-client.php',
            'lara-oauth2-client'
        );

        $this->app->singleton(OAuth2Client::class, function ($app) {
            return new OAuth2Client(
                config('lara-oauth2-client.client_id', ''),
                config('lara-oauth2-client.client_secret', ''),
                config('lara-oauth2-client.redirect_uri', '/oauth2/callback'),
                config('lara-oauth2-client.authorization_url', ''),
                config('lara-oauth2-client.token_url', ''),
                config('lara-oauth2-client.user_info_url', ''),
                config('lara-oauth2-client.scopes', [])
            );
        });

        $this->app->singleton(UserService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPublishables();
        $this->registerRoutes();
        $this->registerMiddleware();
        $this->registerCommands();
        $this->registerFortifyIntegration();
        $this->registerJetstreamIntegration();
    }

    /**
     * Register publishable assets.
     */
    protected function registerPublishables(): void
    {
        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lara-oauth2-client');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/lara-oauth2-client.php' => config_path('lara-oauth2-client.php'),
            ], 'lara-oauth2-client-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'lara-oauth2-client-migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/lara-oauth2-client'),
            ], 'lara-oauth2-client-views');
        }
    }

    /**
     * Register package routes.
     */
    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => config('lara-oauth2-client.route_prefix', 'oauth2'),
            'middleware' => config('lara-oauth2-client.route_middleware', ['web']),
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('oauth2.auth', OAuth2Authenticate::class);
    }

    /**
     * Register console commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    /**
     * Register Laravel Fortify integration.
     */
    protected function registerFortifyIntegration(): void
    {
        if (! config('lara-oauth2-client.fortify_enabled', false)) {
            return;
        }

        if (class_exists(\Laravel\Fortify\Fortify::class)) {
            // Add SSO login view
            \Laravel\Fortify\Fortify::loginView(function () {
                return view('lara-oauth2-client::sso-login');
            });
        }
    }

    /**
     * Register Laravel Jetstream integration.
     */
    protected function registerJetstreamIntegration(): void
    {
        if (! config('lara-oauth2-client.jetstream_enabled', false)) {
            return;
        }

        if (class_exists(\Laravel\Jetstream\Jetstream::class)) {
            $this->app->register(\Larawizards\LaraOAuth2Client\Jetstream\OAuth2JetstreamServiceProvider::class);
        }
    }
}
