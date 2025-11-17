<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Passport\Passport;

test('authenticated user can list all users with pagination', function () {
    $user = User::factory()->create();
    User::factory()->count(20)->create();

    Passport::actingAs($user);

    $response = $this->getJson('/api/users?per_page=10');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => ['*' => ['id', 'name', 'email', 'created_at']],
            'meta' => ['total', 'per_page', 'current_page', 'last_page'],
        ]);

    expect($response->json('meta.per_page'))->toBeIn([10, [10, 10]]);
});

test('authenticated user can search users', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);
    User::factory()->create(['name' => 'Bob Johnson']);

    Passport::actingAs($user);

    $response = $this->getJson('/api/users?search=John');

    $response->assertSuccessful();
    expect($response->json('data'))->toHaveCount(2);
});

test('authenticated user can view specific user', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create([
        'name' => 'Target User',
        'email' => 'target@example.com',
    ]);

    Passport::actingAs($user);

    $response = $this->getJson("/api/users/{$targetUser->id}");

    $response->assertSuccessful()
        ->assertJson([
            'user' => [
                'id' => $targetUser->id,
                'name' => 'Target User',
                'email' => 'target@example.com',
            ],
        ]);
});

test('user can update their own profile', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    Passport::actingAs($user);

    $response = $this->putJson("/api/users/{$user->id}", [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'message' => 'Usuario actualizado exitosamente',
            'user' => [
                'name' => 'New Name',
                'email' => 'new@example.com',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);
});

test('user cannot update another user profile', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Passport::actingAs($user);

    $response = $this->putJson("/api/users/{$otherUser->id}", [
        'name' => 'Hacked Name',
    ]);

    $response->assertForbidden();
});

test('user can delete another user', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    Passport::actingAs($user);

    $response = $this->deleteJson("/api/users/{$targetUser->id}");

    $response->assertSuccessful()
        ->assertJson(['message' => 'Usuario eliminado exitosamente']);

    $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
});

test('user cannot delete themselves', function () {
    $user = User::factory()->create();

    Passport::actingAs($user);

    $response = $this->deleteJson("/api/users/{$user->id}");

    $response->assertForbidden()
        ->assertJson(['message' => 'No puedes eliminar tu propio usuario']);
});

test('unauthenticated user cannot access users endpoints', function () {
    $user = User::factory()->create();

    $this->getJson('/api/users')->assertUnauthorized();
    $this->getJson("/api/users/{$user->id}")->assertUnauthorized();
    $this->putJson("/api/users/{$user->id}", ['name' => 'Test'])->assertUnauthorized();
    $this->deleteJson("/api/users/{$user->id}")->assertUnauthorized();
});
