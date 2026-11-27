<?php

namespace Tests\Feature\Api\V1;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_users(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $other = User::factory()->accountant()->create();

        $this->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonFragment(['email' => $other->email]);
    }

    public function test_admin_can_create_a_user(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $response = $this->postJson('/api/v1/users', [
            'name' => 'Dana Dispatcher',
            'email' => 'dana@example.com',
            'password' => 'super-secret',
            'role' => UserRole::Dispatcher->value,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('email', 'dana@example.com');
        $response->assertJsonPath('role', UserRole::Dispatcher->value);

        $user = User::where('email', 'dana@example.com')->firstOrFail();

        $this->assertSame(UserRole::Dispatcher, $user->role);
        $this->assertTrue(Hash::check('super-secret', $user->password));
    }

    public function test_create_user_validates_unique_email_and_role(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/v1/users', [
            'name' => 'Dana',
            'email' => 'taken@example.com',
            'password' => 'super-secret',
            'role' => 'superadmin',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'role']);
    }

    public function test_non_admin_cannot_list_users(): void
    {
        Sanctum::actingAs(User::factory()->dispatcher()->create());

        $this->getJson('/api/v1/users')->assertForbidden();
    }

    public function test_accountant_cannot_create_users(): void
    {
        Sanctum::actingAs(User::factory()->accountant()->create());

        $this->postJson('/api/v1/users', [
            'name' => 'Dana',
            'email' => 'dana@example.com',
            'password' => 'super-secret',
            'role' => UserRole::Dispatcher->value,
        ])->assertForbidden();
    }

    public function test_guests_cannot_access_user_management(): void
    {
        $this->getJson('/api/v1/users')->assertUnauthorized();
    }

    public function test_admin_can_update_a_user_role(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $user = User::factory()->dispatcher()->create();

        $response = $this->putJson("/api/v1/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'role' => UserRole::Accountant->value,
        ]);

        $response->assertOk();
        $response->assertJsonPath('role', UserRole::Accountant->value);
        $this->assertSame(UserRole::Accountant, $user->refresh()->role);
    }

    public function test_admin_can_delete_a_user(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $user = User::factory()->create();

        $this->deleteJson("/api/v1/users/{$user->id}")->assertOk();

        $this->assertModelMissing($user);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $this->deleteJson("/api/v1/users/{$admin->id}")->assertStatus(422);

        $this->assertModelExists($admin);
    }
}
