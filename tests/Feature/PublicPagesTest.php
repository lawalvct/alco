<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_homepage_renders_the_selected_public_page(): void
    {
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->etc());
    }

    public function test_homepage_navigation_destinations_render(): void
    {
        foreach ([
            '/about' => 'about',
            '/services' => 'services',
            '/insights' => 'insights',
            '/tools' => 'tools',
            '/contact' => 'contact',
            '/book-consultation' => 'consultation',
        ] as $path => $kind) {
            $this->get($path)->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('Public/Section')
                ->where('kind', $kind)
                ->etc());
        }
    }
}
