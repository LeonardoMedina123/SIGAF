<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\UserActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_and_mark_their_notification_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new UserActivityNotification(
            'invoice_uploaded',
            'Factura recibida',
            'La factura de la orden OC-2026-100 fue recibida.',
        ));
        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Factura recibida')
            ->assertSee('La factura de la orden OC-2026-100 fue recibida.')
            ->assertSee('Marcar como leída');

        $this->post(route('notifications.read', $notification->id))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success', 'La notificación se marcó como leída.');

        $this->assertNotNull($notification->fresh()->read_at);

        $this->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Leída')
            ->assertDontSee('Marcar como leída');
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $owner->notify(new UserActivityNotification(
            'payment_validated',
            'Pago validado',
            'El pago de la orden OC-2026-101 fue validado.',
        ));
        $notification = $owner->notifications()->firstOrFail();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->post(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }
}
