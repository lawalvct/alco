<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_the_admin_login_address_hands_over_to_the_single_site_login(): void
    {
        $this->get('/admin/login')->assertRedirect(route('login'));
    }

    public function test_administrators_land_on_the_admin_dashboard_after_signing_in(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->post(route('login'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/admin');
    }

    public function test_signing_in_from_the_login_form_does_a_full_page_load_to_the_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // The login form is submitted by Inertia; the admin is not an Inertia page.
        $this->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('login'), ['email' => $admin->email, 'password' => 'password'])
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', url('/admin'));
    }

    public function test_regular_users_cannot_access_the_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_designated_administrators_can_access_the_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        $this->actingAs($user)->get('/admin')->assertOk();
    }
}
