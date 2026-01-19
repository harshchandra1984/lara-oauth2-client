<?php

namespace Larawizards\LaraOAuth2Client\Tests;

use Larawizards\LaraOAuth2Client\Http\Controllers\OAuth2Controller;
use Larawizards\LaraOAuth2Client\OAuth2Client;
use Larawizards\LaraOAuth2Client\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class OAuth2ControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register routes for testing
        Route::middleware('web')->group(function () {
            // Register login route (required by controller redirects)
            Route::get('/login', function () {
                return response('Login page', 200);
            })->name('login');

            // Register home route (required by controller redirects)
            Route::get('/home', function () {
                return response('Home page', 200);
            })->name('home');

            // Register OAuth2 routes
            Route::get('/oauth2/redirect', [OAuth2Controller::class, 'redirect'])->name('oauth2.redirect');
            Route::get('/oauth2/callback', [OAuth2Controller::class, 'callback'])->name('oauth2.callback');
        });
    }

    public function test_redirect_route_returns_redirect_response(): void
    {
        $response = $this->get('/oauth2/redirect');

        $response->assertRedirect();
    }

    public function test_redirect_stores_intended_url_in_session(): void
    {
        $response = $this->get('/oauth2/redirect?redirect=/dashboard');

        $this->assertTrue(session()->has('oauth2_intended_url'));
        $this->assertEquals('/dashboard', session()->get('oauth2_intended_url'));
    }

    public function test_callback_handles_missing_code(): void
    {
        $response = $this->get('/oauth2/callback');

        $response->assertRedirect();
        $response->assertSessionHasErrors('oauth2');
    }

    public function test_callback_handles_missing_state(): void
    {
        $response = $this->get('/oauth2/callback?code=test-code');

        $response->assertRedirect();
        $response->assertSessionHasErrors('oauth2');
    }

    public function test_callback_handles_oauth_error(): void
    {
        $response = $this->get('/oauth2/callback?error=access_denied');

        $response->assertRedirect();
        $response->assertSessionHasErrors('oauth2');
    }
}
