<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginRouteTest extends TestCase
{
    public function test_login_route_exists(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'demo@example.com',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/');
    }
}
