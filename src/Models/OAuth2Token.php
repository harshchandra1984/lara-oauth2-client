<?php

namespace Larawizards\LaraOAuth2Client\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OAuth2Token extends Model
{
    protected $table = 'oauth2_tokens';

    protected $fillable = [
        'user_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'token_type',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Get the user that owns the token.
     */
    public function user(): BelongsTo
    {
        $userModel = config('lara-oauth2-client.user_model', \App\Models\User::class);

        return $this->belongsTo($userModel);
    }

    /**
     * Check if the token is expired.
     */
    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Get the decrypted access token.
     */
    public function getAccessTokenAttribute($value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    /**
     * Set the encrypted access token.
     */
    public function setAccessTokenAttribute($value): void
    {
        $this->attributes['access_token'] = $value ? encrypt($value) : null;
    }

    /**
     * Get the decrypted refresh token.
     */
    public function getRefreshTokenAttribute($value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    /**
     * Set the encrypted refresh token.
     */
    public function setRefreshTokenAttribute($value): void
    {
        $this->attributes['refresh_token'] = $value ? encrypt($value) : null;
    }
}
