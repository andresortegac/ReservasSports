<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_displays_the_login_screen(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Iniciar sesión');
    }

    public function test_guests_are_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/home');

        $response->assertRedirect('/');
    }

    public function test_user_can_log_in_and_see_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@reservassports.com',
            'password' => 'admin12345',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'admin12345',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }
}
