<?php

namespace Larawizards\LaraOAuth2Client\Tests;

use Larawizards\LaraOAuth2Client\Http\Middleware\OAuth2Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class MiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register OAuth2 routes (required for middleware redirects)
        Route::middleware('web')->group(function () {
            Route::get('/oauth2/redirect', function () {
                return redirect('https://example.com/oauth/authorize');
            })->name('oauth2.redirect');
        });

        // Create a test route protected by middleware
        Route::middleware(['web', 'oauth2.auth'])->get('/protected', function () {
            return response()->json(['message' => 'Protected route']);
        });
    }

    public function test_middleware_redirects_unauthenticated_users(): void
    {
        $response = $this->get('/protected');

        $response->assertRedirect();
        $this->assertStringContainsString('/oauth2/redirect', $response->headers->get('Location'));
    }

    public function test_middleware_allows_authenticated_users(): void
    {
        // Create a simple user model for testing
        if (! \Schema::hasTable('users')) {
            \Schema::create('users', function ($table) {
                $table->id();
                $table->string('email');
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }

        $user = new class extends \Illuminate\Foundation\Auth\User {
            protected $table = 'users';
        };
        $user->id = 1;
        $user->email = 'test@example.com';

        Auth::login($user);

        $response = $this->get('/protected');

        $response->assertOk();
        $response->assertJson(['message' => 'Protected route']);
    }

    public function test_middleware_returns_json_for_api_requests(): void
    {
        $request = Request::create('/protected', 'GET');
        $request->headers->set('Accept', 'application/json');

        $middleware = new OAuth2Authenticate();
        $response = $middleware->handle($request, function ($req) {
            return response()->json(['message' => 'OK']);
        });

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }
}
