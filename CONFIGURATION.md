# Configuration Guide

This guide will walk you through configuring the Lara OAuth2 Client package step by step.

## Step 1: Install the Package

```bash
composer require larawizards/lara-oauth2-client
```

## Step 2: Publish Configuration Files

Publish the configuration file:

```bash
php artisan vendor:publish --tag=lara-oauth2-client-config
```

Or use the install command which publishes everything:

```bash
php artisan lara-oauth2-client:install
```

This will create `config/lara-oauth2-client.php` in your Laravel application.

## Step 3: Configure Environment Variables

Add the following to your `.env` file:

### Required Configuration

```env
# OAuth2 Provider Credentials
OAUTH2_CLIENT_ID=your-client-id-here
OAUTH2_CLIENT_SECRET=your-client-secret-here

# OAuth2 Endpoints
OAUTH2_AUTHORIZATION_URL=https://your-provider.com/oauth/authorize
OAUTH2_TOKEN_URL=https://your-provider.com/oauth/token
OAUTH2_USER_INFO_URL=https://your-provider.com/oauth/userinfo

# Redirect URI (must match your OAuth2 provider configuration)
OAUTH2_REDIRECT_URI=http://localhost:8000/oauth2/callback
```

### Optional Configuration

```env
# OAuth2 Scopes (space-separated)
OAUTH2_SCOPES=openid profile email

# Route Configuration
OAUTH2_ROUTE_PREFIX=oauth2

# User Model (if different from App\Models\User)
OAUTH2_USER_MODEL=App\Models\User

# Auto-create users if they don't exist
OAUTH2_AUTO_CREATE_USERS=true

# Authentication Options
OAUTH2_REMEMBER_ME=true
OAUTH2_REVOKE_ON_LOGOUT=false

# SSO Login Configuration
OAUTH2_SSO_LOGIN_ENABLED=true
OAUTH2_SSO_LOGIN_ROUTE=/login/sso
OAUTH2_SSO_BUTTON_TEXT=Sign in with SSO
OAUTH2_SSO_BUTTON_CLASS=btn btn-primary

# Fortify/Jetstream Integration
OAUTH2_FORTIFY_ENABLED=false
OAUTH2_JETSTREAM_ENABLED=false

# Route Names (if you have custom routes)
OAUTH2_LOGIN_ROUTE=login
OAUTH2_HOME_ROUTE=home
```

## Step 4: Run Migrations

Publish and run the migrations:

```bash
php artisan vendor:publish --tag=lara-oauth2-client-migrations
php artisan migrate
```

This creates the `oauth2_tokens` table for storing encrypted OAuth2 tokens.

## Step 5: Update Your User Model

Add the necessary columns to your `users` table. Create a migration:

```bash
php artisan make:migration add_oauth2_fields_to_users_table
```

In the migration file:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('oauth2_id')->nullable()->unique()->after('id');
            $table->string('avatar')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['oauth2_id', 'avatar']);
        });
    }
};
```

Run the migration:

```bash
php artisan migrate
```

## Step 6: Configure User Mapping

If your OAuth2 provider returns different attribute names, customize the mapping in `config/lara-oauth2-client.php`:

```php
'user_mapping' => [
    'id' => 'oauth2_id',           // OAuth2 provider's 'id' → your 'oauth2_id' column
    'email' => 'email',             // OAuth2 provider's 'email' → your 'email' column
    'name' => 'name',               // OAuth2 provider's 'name' → your 'name' column
    'first_name' => 'first_name',   // OAuth2 provider's 'first_name' → your 'first_name' column
    'last_name' => 'last_name',     // OAuth2 provider's 'last_name' → your 'last_name' column
    'avatar' => 'avatar',           // OAuth2 provider's 'avatar' → your 'avatar' column
],
```

## Configuration Examples for Popular Providers

### Google OAuth2

```env
OAUTH2_CLIENT_ID=your-google-client-id.apps.googleusercontent.com
OAUTH2_CLIENT_SECRET=your-google-client-secret
OAUTH2_AUTHORIZATION_URL=https://accounts.google.com/o/oauth2/v2/auth
OAUTH2_TOKEN_URL=https://oauth2.googleapis.com/token
OAUTH2_USER_INFO_URL=https://www.googleapis.com/oauth2/v2/userinfo
OAUTH2_REDIRECT_URI=https://your-app.com/oauth2/callback
OAUTH2_SCOPES=openid profile email
```

### Microsoft Azure AD

```env
OAUTH2_CLIENT_ID=your-azure-client-id
OAUTH2_CLIENT_SECRET=your-azure-client-secret
OAUTH2_AUTHORIZATION_URL=https://login.microsoftonline.com/{tenant-id}/oauth2/v2.0/authorize
OAUTH2_TOKEN_URL=https://login.microsoftonline.com/{tenant-id}/oauth2/v2.0/token
OAUTH2_USER_INFO_URL=https://graph.microsoft.com/oidc/userinfo
OAUTH2_REDIRECT_URI=https://your-app.com/oauth2/callback
OAUTH2_SCOPES=openid profile email
```

### GitHub OAuth2

```env
OAUTH2_CLIENT_ID=your-github-client-id
OAUTH2_CLIENT_SECRET=your-github-client-secret
OAUTH2_AUTHORIZATION_URL=https://github.com/login/oauth/authorize
OAUTH2_TOKEN_URL=https://github.com/login/oauth/access_token
OAUTH2_USER_INFO_URL=https://api.github.com/user
OAUTH2_REDIRECT_URI=https://your-app.com/oauth2/callback
OAUTH2_SCOPES=user:email read:user
```

### Auth0

```env
OAUTH2_CLIENT_ID=your-auth0-client-id
OAUTH2_CLIENT_SECRET=your-auth0-client-secret
OAUTH2_AUTHORIZATION_URL=https://your-domain.auth0.com/authorize
OAUTH2_TOKEN_URL=https://your-domain.auth0.com/oauth/token
OAUTH2_USER_INFO_URL=https://your-domain.auth0.com/userinfo
OAUTH2_REDIRECT_URI=https://your-app.com/oauth2/callback
OAUTH2_SCOPES=openid profile email
```

### Okta

```env
OAUTH2_CLIENT_ID=your-okta-client-id
OAUTH2_CLIENT_SECRET=your-okta-client-secret
OAUTH2_AUTHORIZATION_URL=https://your-domain.okta.com/oauth2/default/v1/authorize
OAUTH2_TOKEN_URL=https://your-domain.okta.com/oauth2/default/v1/token
OAUTH2_USER_INFO_URL=https://your-domain.okta.com/oauth2/default/v1/userinfo
OAUTH2_REDIRECT_URI=https://your-app.com/oauth2/callback
OAUTH2_SCOPES=openid profile email
```

## Laravel Fortify Integration

### Step 1: Install Fortify (if not already installed)

```bash
composer require laravel/fortify
php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
php artisan migrate
```

### Step 2: Enable OAuth2 in Fortify

Add to your `.env`:

```env
OAUTH2_FORTIFY_ENABLED=true
```

### Step 3: Configure Fortify

In `config/fortify.php`, you can customize the login view. The package will automatically use the SSO login view when enabled.

## Laravel Jetstream Integration

### Step 1: Install Jetstream (if not already installed)

```bash
composer require laravel/jetstream
php artisan jetstream:install livewire
# or
php artisan jetstream:install inertia
php artisan migrate
```

### Step 2: Enable OAuth2 in Jetstream

Add to your `.env`:

```env
OAUTH2_JETSTREAM_ENABLED=true
```

### Step 3: Publish Views (Optional)

If you want to customize the SSO button in Jetstream login page:

```bash
php artisan vendor:publish --tag=jetstream-views
```

Then edit `resources/views/auth/login.blade.php` and add:

```blade
@if(config('lara-oauth2-client.jetstream_enabled'))
    <div class="mt-4">
        <a href="{{ route('login.sso') }}" class="btn btn-primary w-full">
            {{ config('lara-oauth2-client.sso_button_text') }}
        </a>
    </div>
@endif
```

## Customizing the SSO Login Page

### Step 1: Publish Views

```bash
php artisan vendor:publish --tag=lara-oauth2-client-views
```

### Step 2: Customize

Edit `resources/views/vendor/lara-oauth2-client/sso-login.blade.php` to match your application's design.

## Advanced Configuration

### Custom User Model

If you're using a different user model:

```env
OAUTH2_USER_MODEL=App\Models\Admin
```

### Custom Route Prefix

Change the OAuth2 route prefix:

```env
OAUTH2_ROUTE_PREFIX=auth
```

This will change routes from `/oauth2/*` to `/auth/*`.

### Disable Auto User Creation

If you want to manually handle user creation:

```env
OAUTH2_AUTO_CREATE_USERS=false
```

Then handle user creation in your own code or event listeners.

### Custom Scopes

Request different scopes from your OAuth2 provider:

```env
OAUTH2_SCOPES=openid profile email offline_access
```

## Testing Your Configuration

### 1. Check Routes

```bash
php artisan route:list | grep oauth2
```

You should see:
- `oauth2.redirect`
- `oauth2.callback`
- `oauth2.logout`
- `login.sso` (if enabled)

### 2. Test Redirect

Visit in your browser:
```
http://localhost:8000/oauth2/redirect
```

This should redirect you to your OAuth2 provider's authorization page.

### 3. Check Configuration

```bash
php artisan tinker
```

Then:

```php
config('lara-oauth2-client.client_id');
config('lara-oauth2-client.authorization_url');
```

## Troubleshooting

### "Invalid redirect URI" Error

Make sure `OAUTH2_REDIRECT_URI` in your `.env` exactly matches what's configured in your OAuth2 provider dashboard.

### "Client ID not found" Error

Verify your `OAUTH2_CLIENT_ID` and `OAUTH2_CLIENT_SECRET` are correct in `.env`.

### Routes Not Working

Clear route cache:

```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### User Not Created

1. Check `OAUTH2_AUTO_CREATE_USERS=true` in `.env`
2. Verify user mapping in `config/lara-oauth2-client.php`
3. Check that your user model has the required columns

## Security Best Practices

1. **Never commit `.env` file** - Keep credentials secure
2. **Use HTTPS in production** - OAuth2 requires secure connections
3. **Validate redirect URIs** - Only allow trusted redirect URIs
4. **Rotate secrets regularly** - Change client secrets periodically
5. **Use environment-specific configs** - Different credentials for dev/staging/production

## Next Steps

After configuration:
1. Test the OAuth2 flow
2. Customize the SSO login page
3. Set up Fortify/Jetstream integration (if needed)
4. Add custom user mapping if required
5. Deploy to production with proper HTTPS setup

For usage examples, see the [README.md](README.md).
