<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;

beforeEach(function () {
    $this->artisan('passport:keys');

    $client = Client::create([
        'name' => 'Test Personal Access Client',
        'secret' => 'test-secret',
        'provider' => 'users',
        'redirect_uris' => [],
        'grant_types' => ['personal_access'],
        'revoked' => false,
    ]);

    \DB::table('oauth_personal_access_clients')->insert([
        'client_id' => $client->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

test('user can register', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email'],
            'access_token',
            'token_type',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);
});

test('register validation fails with invalid data', function () {
    $this->postJson('/api/register', [
        'name' => '',
        'email' => 'invalid-email',
        'password' => '123',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email'],
            'access_token',
            'token_type',
        ]);
});

test('user cannot login with invalid credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $response = $this->postJson('/api/logout');

    $response->assertSuccessful()
        ->assertJson(['message' => 'Sesión cerrada exitosamente']);
});

test('authenticated user can get their profile', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    Passport::actingAs($user);

    $response = $this->getJson('/api/me');

    $response->assertSuccessful()
        ->assertJson([
            'user' => [
                'id' => $user->id,
                'name' => 'Test User',
                'email' => 'test@example.com',
            ],
        ]);
});

test('authenticated user can refresh token', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $response = $this->postJson('/api/refresh');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'message',
            'access_token',
            'token_type',
        ]);
});

test('unauthenticated user cannot access protected routes', function () {
    $this->postJson('/api/logout')->assertUnauthorized();
    $this->getJson('/api/me')->assertUnauthorized();
    $this->postJson('/api/refresh')->assertUnauthorized();
});
