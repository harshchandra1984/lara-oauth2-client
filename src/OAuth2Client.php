<?php

namespace Larawizards\LaraOAuth2Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OAuth2Client
{
    protected Client $httpClient;
    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;
    protected string $authorizationUrl;
    protected string $tokenUrl;
    protected string $userInfoUrl;
    protected array $scopes;

    public function __construct(
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        string $authorizationUrl,
        string $tokenUrl,
        string $userInfoUrl,
        array $scopes = []
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $this->normalizeRedirectUri($redirectUri);
        $this->authorizationUrl = $authorizationUrl;
        $this->tokenUrl = $tokenUrl;
        $this->userInfoUrl = $userInfoUrl;
        $this->scopes = $scopes;

        $this->httpClient = new Client([
            'timeout' => 30,
            'http_errors' => true,
        ]);
    }

    /**
     * Generate the authorization URL.
     */
    public function getAuthorizationUrl(?string $state = null): string
    {
        $state = $state ?? Str::random(40);

        // Store state in cache for validation
        Cache::put("oauth2_state_{$state}", true, 600); // 10 minutes

        $params = [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => implode(' ', $this->scopes),
            'state' => $state,
        ];

        return $this->authorizationUrl.'?'.http_build_query($params);
    }

    /**
     * Exchange authorization code for access token.
     *
     * @throws GuzzleException
     */
    public function getAccessToken(string $code, string $state): array
    {
        // Validate state
        if (! Cache::has("oauth2_state_{$state}")) {
            throw new \RuntimeException('Invalid state parameter');
        }

        Cache::forget("oauth2_state_{$state}");

        $response = $this->httpClient->post($this->tokenUrl, [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (! isset($data['access_token'])) {
            throw new \RuntimeException('Failed to obtain access token');
        }

        return $data;
    }

    /**
     * Get user information from the OAuth2 provider.
     *
     * @throws GuzzleException
     */
    public function getUserInfo(string $accessToken): array
    {
        $response = $this->httpClient->get($this->userInfoUrl, [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Normalize redirect URI to ensure it's a full URL.
     */
    protected function normalizeRedirectUri(string $redirectUri): string
    {
        // If it's already a full URL, return as is
        if (filter_var($redirectUri, FILTER_VALIDATE_URL)) {
            return $redirectUri;
        }

        // If it starts with /, prepend the app URL
        if (str_starts_with($redirectUri, '/')) {
            return url($redirectUri);
        }

        // Otherwise, assume it's a path and prepend /
        return url('/'.$redirectUri);
    }

    /**
     * Refresh an access token using a refresh token.
     *
     * @throws GuzzleException
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $response = $this->httpClient->post($this->tokenUrl, [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (! isset($data['access_token'])) {
            throw new \RuntimeException('Failed to refresh access token');
        }

        return $data;
    }
}
