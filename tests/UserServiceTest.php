<?php

namespace Larawizards\LaraOAuth2Client\Tests;

use Larawizards\LaraOAuth2Client\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = app(UserService::class);

        // Create users table for testing
        if (! \Schema::hasTable('users')) {
            \Schema::create('users', function ($table) {
                $table->id();
                $table->string('oauth2_id')->nullable();
                $table->string('email')->unique();
                $table->string('name')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_can_map_oauth2_attributes_to_user_attributes(): void
    {
        $userInfo = [
            'id' => 'oauth-123',
            'email' => 'test@example.com',
            'name' => 'John Doe',
            'email_verified' => true,
        ];

        $tokenData = [
            'access_token' => 'test-token',
            'token_type' => 'Bearer',
        ];

        // Mock user model
        $userModel = new class extends \Illuminate\Database\Eloquent\Model {
            protected $table = 'users';
            protected $fillable = ['oauth2_id', 'email', 'name', 'first_name', 'last_name', 'email_verified_at'];
        };

        config(['lara-oauth2-client.user_model' => get_class($userModel)]);

        $user = $this->userService->createOrUpdateUser($userInfo, $tokenData);

        $this->assertNotNull($user);
        $this->assertEquals('oauth-123', $user->oauth2_id);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('John Doe', $user->name);
    }

    public function test_handles_name_splitting(): void
    {
        $userInfo = [
            'id' => 'oauth-123',
            'email' => 'test@example.com',
            'name' => 'John Doe',
        ];

        $tokenData = ['access_token' => 'test-token'];

        $userModel = new class extends \Illuminate\Database\Eloquent\Model {
            protected $table = 'users';
            protected $fillable = ['oauth2_id', 'email', 'name', 'first_name', 'last_name'];
        };

        config([
            'lara-oauth2-client.user_model' => get_class($userModel),
            'lara-oauth2-client.user_mapping' => [
                'id' => 'oauth2_id',
                'email' => 'email',
                'name' => 'name',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
            ],
        ]);

        $user = $this->userService->createOrUpdateUser($userInfo, $tokenData);

        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);
    }
}
