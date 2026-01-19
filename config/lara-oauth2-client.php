<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OAuth2 Client ID
    |--------------------------------------------------------------------------
    |
    | Your OAuth2 client ID from the OAuth2 provider.
    |
    */
    'client_id' => env('OAUTH2_CLIENT_ID'),

    /*
    |--------------------------------------------------------------------------
    | OAuth2 Client Secret
    |--------------------------------------------------------------------------
    |
    | Your OAuth2 client secret from the OAuth2 provider.
    |
    */
    'client_secret' => env('OAUTH2_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | OAuth2 Redirect URI
    |--------------------------------------------------------------------------
    |
    | The redirect URI registered with your OAuth2 provider.
    | This should match exactly with what's configured in your OAuth2 provider.
    |
    */
    'redirect_uri' => env('OAUTH2_REDIRECT_URI', '/oauth2/callback'),

    /*
    |--------------------------------------------------------------------------
    | OAuth2 Authorization URL
    |--------------------------------------------------------------------------
    |
    | The authorization endpoint URL of your OAuth2 provider.
    |
    */
    'authorization_url' => env('OAUTH2_AUTHORIZATION_URL'),

    /*
    |--------------------------------------------------------------------------
    | OAuth2 Token URL
    |--------------------------------------------------------------------------
    |
    | The token endpoint URL of your OAuth2 provider.
    |
    */
    'token_url' => env('OAUTH2_TOKEN_URL'),

    /*
    |--------------------------------------------------------------------------
    | OAuth2 User Info URL
    |--------------------------------------------------------------------------
    |
    | The user info endpoint URL to fetch authenticated user information.
    |
    */
    'user_info_url' => env('OAUTH2_USER_INFO_URL'),

    /*
    |--------------------------------------------------------------------------
    | OAuth2 Scopes
    |--------------------------------------------------------------------------
    |
    | The scopes to request during OAuth2 authorization.
    |
    */
    'scopes' => env('OAUTH2_SCOPES', 'openid profile email') ? explode(' ', env('OAUTH2_SCOPES', 'openid profile email')) : ['openid', 'profile', 'email'],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for package routes.
    |
    */
    'route_prefix' => env('OAUTH2_ROUTE_PREFIX', 'oauth2'),
    'route_middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model class to use for authentication.
    |
    */
    'user_model' => env('OAUTH2_USER_MODEL', \App\Models\User::class),

    /*
    |--------------------------------------------------------------------------
    | User Mapping
    |--------------------------------------------------------------------------
    |
    | Map OAuth2 user attributes to your user model attributes.
    | Keys are OAuth2 provider attributes, values are your model attributes.
    |
    */
    'user_mapping' => [
        'id' => 'oauth2_id',
        'email' => 'email',
        'name' => 'name',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'avatar' => 'avatar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Create Users
    |--------------------------------------------------------------------------
    |
    | Whether to automatically create users if they don't exist.
    |
    */
    'auto_create_users' => env('OAUTH2_AUTO_CREATE_USERS', true),

    /*
    |--------------------------------------------------------------------------
    | Fortify Integration
    |--------------------------------------------------------------------------
    |
    | Enable Laravel Fortify integration for SSO login.
    |
    */
    'fortify_enabled' => env('OAUTH2_FORTIFY_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Jetstream Integration
    |--------------------------------------------------------------------------
    |
    | Enable Laravel Jetstream integration for SSO login.
    |
    */
    'jetstream_enabled' => env('OAUTH2_JETSTREAM_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | SSO Login Page
    |--------------------------------------------------------------------------
    |
    | Configuration for the SSO login page.
    |
    */
    'sso_login_enabled' => env('OAUTH2_SSO_LOGIN_ENABLED', true),
    'sso_login_route' => env('OAUTH2_SSO_LOGIN_ROUTE', '/login/sso'),
    'sso_button_text' => env('OAUTH2_SSO_BUTTON_TEXT', 'Sign in with SSO'),
    'sso_button_class' => env('OAUTH2_SSO_BUTTON_CLASS', 'btn btn-primary'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Options
    |--------------------------------------------------------------------------
    |
    | Additional authentication configuration.
    |
    */
    'remember_me' => env('OAUTH2_REMEMBER_ME', true),
    'revoke_on_logout' => env('OAUTH2_REVOKE_ON_LOGOUT', false),

    /*
    |--------------------------------------------------------------------------
    | Route Names
    |--------------------------------------------------------------------------
    |
    | Custom route names for login and home redirects.
    |
    */
    'login_route' => env('OAUTH2_LOGIN_ROUTE', 'login'),
    'home_route' => env('OAUTH2_HOME_ROUTE', 'home'),
];
