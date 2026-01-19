@if (config('lara-oauth2-client.jetstream_enabled', false))
    <div class="mt-4">
        <x-jetstream::button type="button" class="w-full justify-center" onclick="window.location.href='{{ route('login.sso') }}'">
            {{ config('lara-oauth2-client.sso_button_text', 'Sign in with SSO') }}
        </x-jetstream::button>
    </div>
@endif
