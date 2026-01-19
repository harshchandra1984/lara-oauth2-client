<?php

use Larawizards\LaraOAuth2Client\Http\Controllers\OAuth2Controller;
use Illuminate\Support\Facades\Route;

Route::get('/redirect', [OAuth2Controller::class, 'redirect'])
    ->name('oauth2.redirect');

Route::get('/callback', [OAuth2Controller::class, 'callback'])
    ->name('oauth2.callback');

Route::post('/logout', [OAuth2Controller::class, 'logout'])
    ->middleware('auth')
    ->name('oauth2.logout');

// SSO Login route
if (config('lara-oauth2-client.sso_login_enabled', true)) {
    Route::get(config('lara-oauth2-client.sso_login_route', '/login/sso'), function () {
        return redirect()->route('oauth2.redirect');
    })->name('login.sso');
}
