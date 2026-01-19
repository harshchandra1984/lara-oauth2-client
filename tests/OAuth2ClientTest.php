<?php

namespace Larawizards\LaraOAuth2Client\Tests;

use Larawizards\LaraOAuth2Client\OAuth2Client;
use Illuminate\Support\Facades\Cache;

class OAuth2ClientTest extends TestCase
{
    protected OAuth2Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new OAuth2Client(
            'test-client-id',
            'test-client-secret',
            'http://localhost/oauth2/callback',
            'https://example.com/oauth/authorize',
            'https://example.com/oauth/token',
            'https://example.com/oauth/userinfo',
            ['openid', 'profile', 'email']
        );
    }

    public function test_can_generate_authorization_url(): void
    {
        $url = $this->client->getAuthorizationUrl();

        $this->assertStringContainsString('https://example.com/oauth/authorize', $url);
        $this->assertStringContainsString('client_id=test-client-id', $url);
        $this->assertStringContainsString('redirect_uri=', $url);
        $this->assertStringContainsString('response_type=code', $url);
        $this->assertStringContainsString('scope=', $url);
        $this->assertStringContainsString('state=', $url);
    }

    public function test_authorization_url_stores_state_in_cache(): void
    {
        $state = 'test-state-123';
        $this->client->getAuthorizationUrl($state);

        $this->assertTrue(Cache::has("oauth2_state_{$state}"));
    }
}
