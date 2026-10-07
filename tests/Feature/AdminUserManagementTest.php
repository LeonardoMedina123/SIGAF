<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_update_and_delete_users(): void
    {
        $administrator = $this->createUserWithType('administrator', 'admin@example.com');
        $this->actingAs($administrator)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Administrar usuarios')
            ->assertSee('Usuario');

        $departmentId = Department::where('slug', 'departments')->value('id');
        $this->post(route('admin.users.store'), [
            'name' => 'Usuario nuevo',
            'email' => 'nuevo@example.com',
            'department_id' => $departmentId,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'nuevo@example.com')->firstOrFail();
        $this->assertSame((int) $departmentId, (int) $user->department_id);
        $this->assertTrue(Hash::check('Password123!', $user->password));

        $supplierTypeId = Department::where('slug', 'suppliers')->value('id');
        $this->put(route('admin.users.update', $user), [
            'name' => 'Usuario actualizado',
            'email' => 'actualizado@example.com',
            'department_id' => $supplierTypeId,
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $this->assertSame('Usuario actualizado', $user->name);
        $this->assertSame('actualizado@example.com', $user->email);
        $this->assertSame((int) $supplierTypeId, (int) $user->department_id);
        $this->assertTrue(Hash::check('Password123!', $user->password));

        $this->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_purchases_is_a_separate_user_type_from_departments(): void
    {
        $purchases = Department::where('slug', 'purchases')->firstOrFail();
        $departments = Department::where('slug', 'departments')->firstOrFail();

        $this->assertSame('Compras', $purchases->name);
        $this->assertNotSame($departments->id, $purchases->id);

        $administrator = $this->createUserWithType('administrator', 'admin@example.com');
        $this->actingAs($administrator)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Compras')
            ->assertSee('Departamentos');
    }

    public function test_non_administrator_cannot_access_user_management(): void
    {
        $user = $this->createUserWithType('departments', 'department@example.com');

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('Administrar usuarios')
            ->assertDontSee(route('admin.users.index'));

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_last_administrator_cannot_be_demoted_or_deleted(): void
    {
        $administrator = $this->createUserWithType('administrator', 'admin@example.com');
        $this->actingAs($administrator);
        $departmentTypeId = Department::where('slug', 'departments')->value('id');

        $this->put(route('admin.users.update', $administrator), [
            'name' => $administrator->name,
            'email' => $administrator->email,
            'department_id' => $departmentTypeId,
            'password' => '',
            'password_confirmation' => '',
        ])->assertSessionHasErrors('department_id');

        $this->delete(route('admin.users.destroy', $administrator))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $administrator->id]);
    }

    private function createUserWithType(string $slug, string $email): User
    {
        return User::factory()->create([
            'email' => $email,
            'department_id' => Department::where('slug', $slug)->value('id'),
        ]);
    }
}
