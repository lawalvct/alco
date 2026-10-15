<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_the_old_dashboard_address_redirects_to_the_admin_dashboard()
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard'))->assertRedirect('/admin');
    }
}
