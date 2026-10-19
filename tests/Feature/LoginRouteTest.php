<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Database\Seeders\SystemsDepartmentUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_authenticated_user_can_open_the_five_step_order_capture(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Crear nueva orden')
            ->assertSee('id="order-wizard-form"', false)
            ->assertSee('Paso 5: Confirmación');
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

    public function test_supplier_sees_empty_real_orders_and_supplier_navigation(): void
    {
        $supplier = User::factory()->create([
            'department_id' => Department::where('slug', 'suppliers')->value('id'),
        ]);

        $this->actingAs($supplier)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Portal de proveedor')
            ->assertSee('Orden de compra')
            ->assertSee('Factura y XML')
            ->assertSee('Validación de pago')
            ->assertSee('Centro de notificaciones')
            ->assertSee('Ayuda')
            ->assertSee('Cerrar sesión')
            ->assertSee('Aún no hay órdenes de compra para este proveedor.')
            ->assertDontSee('Vista de ejemplo')
            ->assertDontSee('data-invoice-url=', false)
            ->assertDontSee('data-payment-url=', false)
            ->assertDontSee('2515486')
            ->assertDontSee('Catálogo de proveedores')
            ->assertDontSee('Administrar usuarios')
            ->assertDontSee('Crear nueva orden');
    }

    public function test_systems_department_user_can_log_in_and_sees_reimbursement_dashboard(): void
    {
        config([
            'services.systems_department_user.email' => 'departamentoTI@aguascalientes.tecnm.mx',
            'services.systems_department_user.password' => '12345678',
        ]);

        $this->seed(SystemsDepartmentUserSeeder::class);
        $user = User::where('email', 'departamentoTI@aguascalientes.tecnm.mx')->firstOrFail();

        $this->assertTrue(Hash::check('12345678', $user->password));

        $this->post(route('authenticate'), [
            'email' => 'departamentoTI@aguascalientes.tecnm.mx',
            'password' => '12345678',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Mayra Vanessa Capetillo González')
            ->assertSee('Departamento de Sistemas')
            ->assertSee('Orden de reembolso')
            ->assertSee('Cargar documento')
            ->assertSee('Semáforo de estado')
            ->assertSee('Fecha de modificación')
            ->assertSee('Adjunta documento de reembolso')
            ->assertSee('Enviado')
            ->assertSee('En proceso')
            ->assertSee('Completado')
            ->assertSee('Salir')
            ->assertDontSee('Crear nueva orden');
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
