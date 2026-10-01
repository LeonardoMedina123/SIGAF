<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_and_reach_home(): void
    {
        $user = User::factory()->create([
            'email' => 'leonardo@example.com',
            'password' => 'Holamundo',
        ]);

        $response = $this->post(route('authenticate'), [
            'email' => $user->email,
            'password' => 'Holamundo',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
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
    }
}
