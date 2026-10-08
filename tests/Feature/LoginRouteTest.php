<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
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

    public function test_supplier_sees_example_orders_and_supplier_navigation(): void
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
            ->assertSee('Vista de ejemplo')
            ->assertSee('Añadir Factura:')
            ->assertSee('Añadir XML:')
            ->assertSee('data-upload-control data-selected="false"', false)
            ->assertSee('bg-[#073b5c]', false)
            ->assertSee('bg-[#b5b5b5]', false)
            ->assertSee('Modificar factura')
            ->assertSee('Modificar XML')
            ->assertSee('Modificar complemento')
            ->assertSee('data-open-invoice-dialog', false)
            ->assertSee('data-invoice-form', false)
            ->assertSee('data-confirm-invoice', false)
            ->assertSee('data-invoice-submit disabled', false)
            ->assertSee('Adjunta tu archivo')
            ->assertSee('data-open-payment-dialog', false)
            ->assertSee('Añadir complemento de pago:')
            ->assertSee('data-confirm-payment', false)
            ->assertSee('data-payment-submit disabled', false)
            ->assertSee('Antes de enviar estos documentos')
            ->assertSee('ENVIAR')
            ->assertDontSee('Catálogo de proveedores')
            ->assertDontSee('Administrar usuarios')
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
