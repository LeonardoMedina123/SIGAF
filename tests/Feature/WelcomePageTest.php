<?php

namespace Tests\Feature;

use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    public function test_welcome_page_includes_vite_assets(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeText('Inicio de sesión');
        $response->assertSee('/build/assets/');
    }
}
