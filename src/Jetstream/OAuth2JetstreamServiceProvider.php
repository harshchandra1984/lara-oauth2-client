<?php

namespace Larawizards\LaraOAuth2Client\Jetstream;

use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;

class OAuth2JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! config('lara-oauth2-client.jetstream_enabled', false)) {
            return;
        }

        // Add OAuth2 login option to Jetstream views
        $this->loadViewsFrom(__DIR__.'/../../resources/views/jetstream', 'lara-oauth2-client');

        // Share OAuth2 login route with views
        view()->composer('auth.login', function ($view) {
            $view->with('oauth2LoginRoute', route('login.sso'));
            $view->with('oauth2ButtonText', config('lara-oauth2-client.sso_button_text', 'Sign in with SSO'));
            $view->with('oauth2ButtonClass', config('lara-oauth2-client.sso_button_class', 'btn btn-primary'));
        });
    }
}
