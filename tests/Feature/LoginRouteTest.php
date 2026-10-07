<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_and_reach_home(): void
    {
        $administratorTypeId = Department::where('slug', 'administrator')->value('id');
        $user = User::factory()->create([
            'email' => 'leonardo@example.com',
            'password' => 'Holamundo',
            'department_id' => $administratorTypeId,
        ]);

        $response = $this->post(route('authenticate'), [
            'email' => $user->email,
            'password' => 'Holamundo',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_can_open_the_six_step_order_capture(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Crear nueva orden')
            ->assertSee('id="order-wizard-form"', false)
            ->assertSee('Paso 6: Confirmación');
    }

    public function test_administrator_sees_user_management_navigation(): void
    {
        $administrator = User::factory()->create([
            'department_id' => Department::where('slug', 'administrator')->value('id'),
        ]);

        $this->actingAs($administrator)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Administrar usuarios');
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'leonardo@example.com',
            'password' => 'Holamundo',
        ]);

        $response = $this->from('/')->post(route('authenticate'), [
            'email' => 'leonardo@example.com',
            'password' => 'incorrecta',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_home_requires_authentication(): void
    {
        $this->get(route('home'))->assertRedirect('/');
        $this->get(route('suppliers.index'))->assertRedirect('/');
    }
}
