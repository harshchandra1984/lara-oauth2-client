# Lara OAuth2 Client

A Laravel package for OAuth2 client authentication with single sign-on (SSO) support, compatible with Laravel 10, 11, and 12. Includes seamless integration with Laravel Fortify and Jetstream.

## Features

- 🔐 OAuth2 client implementation following industry best practices
- 🚀 Single Sign-On (SSO) login page
- 🔗 Laravel Fortify integration
- 🔗 Laravel Jetstream integration
- 📦 Laravel 10, 11, and 12 compatible
- 🔒 Secure token storage with encryption
- 👤 Automatic user creation/update
- 🎨 Beautiful, customizable SSO login page
- 🧪 Well-tested with PHPUnit

## Installation

You can install the package via Composer:

```bash
composer require larawizards/lara-oauth2-client
```

## Configuration

### Quick Setup

1. **Install the package:**
   ```bash
   composer require larawizards/lara-oauth2-client
   ```

2. **Publish configuration:**
   ```bash
   php artisan lara-oauth2-client:install
   ```

3. **Configure your `.env` file:**
   ```env
   OAUTH2_CLIENT_ID=your-client-id
   OAUTH2_CLIENT_SECRET=your-client-secret
   OAUTH2_REDIRECT_URI=http://your-app.com/oauth2/callback
   OAUTH2_AUTHORIZATION_URL=https://your-provider.com/oauth/authorize
   OAUTH2_TOKEN_URL=https://your-provider.com/oauth/token
   OAUTH2_USER_INFO_URL=https://your-provider.com/oauth/userinfo
   OAUTH2_SCOPES=openid profile email
   ```

4. **Run migrations:**
   ```bash
   php artisan migrate
   ```

### Detailed Configuration

For complete configuration instructions, including:
- Step-by-step setup guide
- Configuration examples for popular providers (Google, Microsoft, GitHub, Auth0, Okta)
- Fortify/Jetstream integration setup
- Custom user mapping
- Advanced options

See [CONFIGURATION.md](CONFIGURATION.md) for detailed instructions.

## Usage

### Basic Usage

The package automatically registers routes for OAuth2 authentication:

- `GET /oauth2/redirect` - Redirects to OAuth2 provider
- `GET /oauth2/callback` - Handles OAuth2 callback
- `POST /oauth2/logout` - Logout and optionally revoke tokens
- `GET /login/sso` - SSO login page (if enabled)

### Using the SSO Login Page

Simply redirect users to the SSO login route:

```php
return redirect()->route('login.sso');
```

Or use the OAuth2 redirect directly:

```php
return redirect()->route('oauth2.redirect');
```

### Protecting Routes with Middleware

Use the `oauth2.auth` middleware to protect routes:

```php
Route::middleware(['oauth2.auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    });
});
```

### User Model Configuration

The package automatically maps OAuth2 user attributes to your user model. You can customize the mapping in `config/lara-oauth2-client.php`:

```php
'user_mapping' => [
    'id' => 'oauth2_id',
    'email' => 'email',
    'name' => 'name',
    'first_name' => 'first_name',
    'last_name' => 'last_name',
    'avatar' => 'avatar',
],
```

Make sure your user model has the necessary columns. You may need to create a migration:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('oauth2_id')->nullable()->unique();
    $table->string('avatar')->nullable();
});
```

### Laravel Fortify Integration

1. Enable Fortify integration in your `.env`:

```env
OAUTH2_FORTIFY_ENABLED=true
```

2. The package will automatically integrate with Fortify's login views.

### Laravel Jetstream Integration

1. Enable Jetstream integration in your `.env`:

```env
OAUTH2_JETSTREAM_ENABLED=true
```

2. Publish Jetstream views (if not already done):

```bash
php artisan jetstream:install livewire
# or
php artisan jetstream:install inertia
```

3. The package will add an SSO login button to your Jetstream login page.

### Customizing Views

Publish the views to customize them:

```bash
php artisan vendor:publish --tag=lara-oauth2-client-views
```

Views will be published to `resources/views/vendor/lara-oauth2-client/`.

### Programmatic Usage

You can also use the OAuth2 client directly:

```php
use Larawizards\LaraOAuth2Client\OAuth2Client;

$client = app(OAuth2Client::class);

// Get authorization URL
$authUrl = $client->getAuthorizationUrl();

// Get access token (after receiving authorization code)
$tokenData = $client->getAccessToken($code, $state);

// Get user info
$userInfo = $client->getUserInfo($tokenData['access_token']);

// Refresh token
$newTokenData = $client->refreshAccessToken($refreshToken);
```

## Configuration Options

All configuration options are available in `config/lara-oauth2-client.php`:

| Option | Description | Default |
|--------|-------------|---------|
| `client_id` | OAuth2 client ID | - |
| `client_secret` | OAuth2 client secret | - |
| `redirect_uri` | OAuth2 redirect URI | `/oauth2/callback` |
| `authorization_url` | OAuth2 authorization endpoint | - |
| `token_url` | OAuth2 token endpoint | - |
| `user_info_url` | OAuth2 user info endpoint | - |
| `scopes` | OAuth2 scopes | `['openid', 'profile', 'email']` |
| `route_prefix` | Route prefix for OAuth2 routes | `oauth2` |
| `auto_create_users` | Automatically create users if they don't exist | `true` |
| `fortify_enabled` | Enable Fortify integration | `false` |
| `jetstream_enabled` | Enable Jetstream integration | `false` |
| `sso_login_enabled` | Enable SSO login page | `true` |

## Testing

Run the test suite:

```bash
composer test
```

Or with PHPUnit:

```bash
vendor/bin/phpunit
```

For detailed testing instructions, see [TESTING.md](TESTING.md).

## Security Best Practices

1. **Always use HTTPS** in production for OAuth2 redirects
2. **Store client secrets securely** - never commit them to version control
3. **Use environment variables** for all sensitive configuration
4. **Enable CSRF protection** - the package uses Laravel's built-in CSRF protection
5. **Validate state parameters** - the package automatically validates state to prevent CSRF attacks
6. **Encrypt tokens** - access and refresh tokens are automatically encrypted in the database

## Requirements

- PHP >= 8.2
- Laravel >= 10.0
- Guzzle HTTP Client

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Support

For support, please open an issue on GitHub or contact [harsh@academyofmine.com](mailto:harsh@academyofmine.com).
