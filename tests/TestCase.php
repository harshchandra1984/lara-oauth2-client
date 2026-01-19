<?php

namespace Larawizards\LaraOAuth2Client\Tests;

use Larawizards\LaraOAuth2Client\LaraOAuth2ClientServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            LaraOAuth2ClientServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Set application key for encryption (required for sessions, etc.)
        // Using a fixed test key for consistency (32 bytes = 256 bits for AES-256)
        $app['config']->set('app.key', 'base64:s9JziQFBGReWkDZQy+ys4l/iNJu3g0W7g/w8tJpSqTQ=');

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('lara-oauth2-client.client_id', 'test-client-id');
        $app['config']->set('lara-oauth2-client.client_secret', 'test-client-secret');
        $app['config']->set('lara-oauth2-client.redirect_uri', 'http://localhost/oauth2/callback');
        $app['config']->set('lara-oauth2-client.authorization_url', 'https://example.com/oauth/authorize');
        $app['config']->set('lara-oauth2-client.token_url', 'https://example.com/oauth/token');
        $app['config']->set('lara-oauth2-client.user_info_url', 'https://example.com/oauth/userinfo');
    }
}
