<?php

namespace Larawizards\LaraOAuth2Client\Services;

use Larawizards\LaraOAuth2Client\Models\OAuth2Token;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class UserService
{
    /**
     * Create or update user from OAuth2 user info.
     */
    public function createOrUpdateUser(array $userInfo, array $tokenData): Model
    {
        $userModel = config('lara-oauth2-client.user_model');
        $mapping = config('lara-oauth2-client.user_mapping', []);

        // Map OAuth2 attributes to user model attributes
        $attributes = $this->mapAttributes($userInfo, $mapping);

        // Find or create user
        $oauth2IdField = $mapping['id'] ?? 'oauth2_id';
        $emailField = $mapping['email'] ?? 'email';

        $user = $userModel::where($oauth2IdField, $attributes[$oauth2IdField] ?? null)
            ->orWhere($emailField, $attributes[$emailField] ?? null)
            ->first();

        if (! $user && config('lara-oauth2-client.auto_create_users', true)) {
            $user = new $userModel();
        }

        if ($user) {
            // Update user attributes
            foreach ($attributes as $key => $value) {
                if ($value !== null) {
                    $user->{$key} = $value;
                }
            }

            // Set email verified if not already set
            if (isset($user->email_verified_at) && ! $user->email_verified_at && isset($userInfo['email_verified'])) {
                if ($userInfo['email_verified']) {
                    $user->email_verified_at = now();
                }
            }

            $user->save();
        } else {
            throw new \RuntimeException('User not found and auto-creation is disabled');
        }

        return $user;
    }

    /**
     * Map OAuth2 user info to user model attributes.
     */
    protected function mapAttributes(array $userInfo, array $mapping): array
    {
        $attributes = [];

        foreach ($mapping as $oauth2Key => $modelKey) {
            if (isset($userInfo[$oauth2Key])) {
                $attributes[$modelKey] = $userInfo[$oauth2Key];
            }
        }

        // Handle nested attributes (e.g., user.name.first)
        if (isset($userInfo['name']) && is_string($userInfo['name'])) {
            $nameField = $mapping['name'] ?? 'name';
            $attributes[$nameField] = $userInfo['name'];
        }

        // Handle first_name and last_name from name
        if (isset($userInfo['name']) && is_string($userInfo['name'])) {
            $nameParts = explode(' ', $userInfo['name'], 2);
            if (isset($mapping['first_name'])) {
                $attributes[$mapping['first_name']] = $nameParts[0] ?? null;
            }
            if (isset($mapping['last_name']) && isset($nameParts[1])) {
                $attributes[$mapping['last_name']] = $nameParts[1];
            }
        }

        return $attributes;
    }

    /**
     * Store OAuth2 tokens for the user.
     */
    public function storeTokens(Authenticatable $user, array $tokenData): void
    {
        if (! class_exists(OAuth2Token::class)) {
            return;
        }

        OAuth2Token::updateOrCreate(
            ['user_id' => $user->getAuthIdentifier()],
            [
                'access_token' => encrypt($tokenData['access_token']),
                'refresh_token' => isset($tokenData['refresh_token']) ? encrypt($tokenData['refresh_token']) : null,
                'expires_at' => isset($tokenData['expires_in']) ? now()->addSeconds($tokenData['expires_in']) : null,
                'token_type' => $tokenData['token_type'] ?? 'Bearer',
            ]
        );
    }

    /**
     * Revoke OAuth2 tokens for the user.
     */
    public function revokeTokens(Authenticatable $user): void
    {
        if (! class_exists(OAuth2Token::class)) {
            return;
        }

        OAuth2Token::where('user_id', $user->getAuthIdentifier())->delete();
    }
}
