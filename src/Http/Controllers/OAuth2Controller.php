<?php

namespace Larawizards\LaraOAuth2Client\Http\Controllers;

use Larawizards\LaraOAuth2Client\OAuth2Client;
use Larawizards\LaraOAuth2Client\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OAuth2Controller extends Controller
{
    public function __construct(
        protected OAuth2Client $oauth2Client,
        protected UserService $userService
    ) {
    }

    /**
     * Redirect to OAuth2 provider for authorization.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $state = $request->get('state', Str::random(40));

        // Store intended URL if user was trying to access a protected route
        if ($request->has('redirect')) {
            session()->put('oauth2_intended_url', $request->get('redirect'));
        } elseif (! Auth::check()) {
            session()->put('oauth2_intended_url', url()->previous());
        }

        $authorizationUrl = $this->oauth2Client->getAuthorizationUrl($state);

        return redirect($authorizationUrl);
    }

    /**
     * Handle OAuth2 callback.
     */
    public function callback(Request $request): RedirectResponse
    {
        $code = $request->get('code');
        $state = $request->get('state');
        $error = $request->get('error');

        $loginRoute = config('lara-oauth2-client.login_route', 'login');

        if ($error) {
            return redirect()->route($loginRoute)
                ->withErrors(['oauth2' => "OAuth2 error: {$error}"]);
        }

        if (! $code || ! $state) {
            return redirect()->route($loginRoute)
                ->withErrors(['oauth2' => 'Invalid OAuth2 callback parameters']);
        }

        try {
            // Exchange code for access token
            $tokenData = $this->oauth2Client->getAccessToken($code, $state);

            // Get user information
            $userInfo = $this->oauth2Client->getUserInfo($tokenData['access_token']);

            // Create or update user
            $user = $this->userService->createOrUpdateUser($userInfo, $tokenData);

            // Authenticate user
            Auth::login($user, config('lara-oauth2-client.remember_me', true));

            // Store tokens if needed
            $this->userService->storeTokens($user, $tokenData);

            // Redirect to intended URL or home
            $homeRoute = config('lara-oauth2-client.home_route', 'home');
            $intendedUrl = session()->pull('oauth2_intended_url', function () use ($homeRoute) {
                try {
                    return route($homeRoute);
                } catch (\Exception $e) {
                    return '/';
                }
            });

            return redirect($intendedUrl)->with('success', 'Successfully authenticated via SSO');
        } catch (\Exception $e) {
            return redirect()->route($loginRoute)
                ->withErrors(['oauth2' => 'Authentication failed: '.$e->getMessage()]);
        }
    }

    /**
     * Logout and optionally revoke tokens.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user && config('lara-oauth2-client.revoke_on_logout', false)) {
            $this->userService->revokeTokens($user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $loginRoute = config('lara-oauth2-client.login_route', 'login');

        return redirect()->route($loginRoute)
            ->with('success', 'Successfully logged out');
    }
}
