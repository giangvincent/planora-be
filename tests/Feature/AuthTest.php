<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Api User',
            'email' => 'api@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'timezone' => 'Asia/Ho_Chi_Minh',
        ]);

        $response->assertCreated();

        $this->assertArrayHasKey('token', $response->json('data'));
        $this->assertArrayHasKey('user', $response->json('data'));
    }

    public function test_user_can_login_and_logout(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Api User',
            'email' => 'api@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'timezone' => 'Asia/Ho_Chi_Minh',
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'api@example.com',
            'password' => 'password',
        ]);

        $login->assertOk();

        $token = $login->json('data.token');

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();
    }
}
