<?php

namespace Larawizards\LaraOAuth2Client\Fortify;

use Larawizards\LaraOAuth2Client\OAuth2Client;
use Larawizards\LaraOAuth2Client\Services\UserService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class OAuth2FortifyUserProvider implements UserProvider
{
    public function __construct(
        protected OAuth2Client $oauth2Client,
        protected UserService $userService
    ) {
    }

    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        $userModel = config('lara-oauth2-client.user_model');

        return $userModel::find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        $userModel = config('lara-oauth2-client.user_model');
        $user = $userModel::find($identifier);

        if ($user && $user->getRememberToken() && hash_equals($user->getRememberToken(), $token)) {
            return $user;
        }

        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        $user->setRememberToken($token);
        $user->save();
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        // This is handled by OAuth2 flow, not traditional credentials
        return null;
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        // This is handled by OAuth2 flow, not traditional credentials
        return false;
    }
}
