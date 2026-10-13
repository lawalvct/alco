<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_the_selected_public_page(): void
    {
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->etc());
    }

    public function test_homepage_navigation_destinations_render(): void
    {
        foreach ([
            '/services' => 'services',
        ] as $path => $kind) {
            $this->get($path)->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('Public/Section')
                ->where('kind', $kind)
                ->etc());
        }

        $this->get('/tools')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/ToolsIndex')
            ->has('tools', 11)
            ->etc());

        $this->get('/tools/vat')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/Calculator')
            ->where('tool.slug', 'vat')
            ->etc());

        $this->get('/insights')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/InsightsIndex')
            ->etc());

        $this->get('/about')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/About')
            ->etc());

        $this->get('/contact')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/Contact')
            ->etc());

        $this->get('/book-consultation')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/Consultation')
            ->where('selectedPackage', null)
            ->etc());
    }
}
