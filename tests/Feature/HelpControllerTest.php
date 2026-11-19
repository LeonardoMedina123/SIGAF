<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_administrator_can_view_admin_contact_options(): void
    {
        $administrator = $this->createUserWithType('administrator', 'admin@example.com');
        $user = $this->createUserWithType('departments', 'user@example.com');

        $this->actingAs($user)
            ->get(route('help.index'))
            ->assertOk()
            ->assertSee('Enviar mensaje por SIGAF')
            ->assertSee('mailto:admin@example.com?subject=Ayuda%20de%20SIGAF', false)
            ->assertSee('+52 449 424 7211')
            ->assertSee('tel:+524494247211', false)
            ->assertSee('https://wa.me/524494247211', false);
    }

    public function test_user_can_send_a_message_to_every_administrator(): void
    {
        $administrator = $this->createUserWithType('administrator', 'admin@example.com');
        $secondAdministrator = $this->createUserWithType('administrator', 'admin2@example.com');
        $user = $this->createUserWithType('departments', 'user@example.com');
        $message = 'Necesito ayuda con mi cuenta.';

        $this->actingAs($user)
            ->post(route('help.messages.store'), ['message' => $message])
            ->assertRedirect(route('help.index'))
            ->assertSessionHas('success', 'Tu mensaje se envió a las cuentas administradoras.');

        $this->assertDatabaseCount('notifications', 2);

        $notification = $administrator->notifications()->firstOrFail();
        $this->assertSame('support_message', $notification->data['event']);
        $this->assertSame('Nuevo mensaje de ayuda de '.$user->name, $notification->data['title']);
        $this->assertSame("Correo: user@example.com\n\n".$message, $notification->data['message']);
        $this->assertSame(1, $secondAdministrator->notifications()->count());
    }

    public function test_administrator_cannot_view_or_send_help_messages(): void
    {
        $administrator = $this->createUserWithType('administrator', 'admin@example.com');

        $this->actingAs($administrator)
            ->get(route('help.index'))
            ->assertForbidden();

        $this->post(route('help.messages.store'), ['message' => 'Mensaje no permitido.'])
            ->assertForbidden();

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_unauthenticated_user_is_redirected_before_opening_help(): void
    {
        $this->get(route('help.index'))
            ->assertRedirect(route('login'));
    }

    public function test_help_message_is_required(): void
    {
        $this->createUserWithType('administrator', 'admin@example.com');
        $user = $this->createUserWithType('departments', 'user@example.com');

        $this->actingAs($user)
            ->post(route('help.messages.store'), ['message' => ''])
            ->assertSessionHasErrors([
                'message' => 'Escribe tu mensaje para poder enviarlo.',
            ]);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_help_message_cannot_exceed_5000_characters(): void
    {
        $this->createUserWithType('administrator', 'admin@example.com');
        $user = $this->createUserWithType('departments', 'user@example.com');

        $this->actingAs($user)
            ->post(route('help.messages.store'), ['message' => str_repeat('a', 5001)])
            ->assertSessionHasErrors([
                'message' => 'El mensaje no puede exceder los 5000 caracteres.',
            ]);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_help_message_is_escaped_in_the_admin_notification_center(): void
    {
        $administrator = $this->createUserWithType('administrator', 'admin@example.com');
        $user = $this->createUserWithType('departments', 'user@example.com');
        $user->forceFill(['name' => '<script>alert(1)</script>'])->save();

        $this->actingAs($user)
            ->post(route('help.messages.store'), ['message' => '<script>alert(2)</script>'])
            ->assertRedirect(route('help.index'));

        $this->actingAs($administrator)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>', false);
    }

    public function test_help_message_returns_an_error_when_no_administrator_exists(): void
    {
        $user = $this->createUserWithType('departments', 'user@example.com');

        $this->actingAs($user)
            ->post(route('help.messages.store'), ['message' => 'Necesito ayuda.'])
            ->assertServiceUnavailable();

        $this->assertDatabaseCount('notifications', 0);
    }

    private function createUserWithType(string $slug, string $email): User
    {
        $department = Department::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => $slug === 'administrator' ? 'Administración' : 'Departamentos'],
        );

        return User::factory()->create([
            'email' => $email,
            'department_id' => $department->id,
        ]);
    }
}
