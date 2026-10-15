<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    public function test_missing_page_has_a_branded_404_and_home_button(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('This page is out of sight.')
            ->assertSee('Return to homepage')
            ->assertSee('href="/"', false)
            ->assertSee('noindex, nofollow');

        $this->assertFileExists(public_path('css/error-pages.css'));
    }

    public function test_server_error_has_a_branded_500_without_exposing_the_exception(): void
    {
        config()->set('app.debug', false);
        Route::get('/__test-server-error', fn () => throw new \RuntimeException('Private diagnostic details'));

        $this->get('/__test-server-error')
            ->assertStatus(500)
            ->assertSee('A small interruption. We’re on it.')
            ->assertSee('Return to homepage')
            ->assertSee('href="/"', false)
            ->assertDontSee('Private diagnostic details');
    }

    public function test_inertia_navigation_hard_loads_the_404_page(): void
    {
        $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'text/html',
        ])->get('/this-page-does-not-exist')
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', url('/this-page-does-not-exist'));
    }

    public function test_inertia_navigation_hard_loads_the_500_page(): void
    {
        config()->set('app.debug', false);
        Route::get('/__test-server-error', fn () => throw new \RuntimeException('Private diagnostic details'));

        $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'text/html',
        ])->get('/__test-server-error')
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', url('/__test-server-error'));
    }

    public function test_json_errors_keep_their_json_response(): void
    {
        $this->getJson('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertJsonStructure(['message']);
    }
}
