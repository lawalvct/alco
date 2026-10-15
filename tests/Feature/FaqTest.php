<?php

namespace Tests\Feature;

use App\Filament\Resources\Faqs\Pages\CreateFaq;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_faq_page_groups_published_questions_in_order(): void
    {
        Faq::factory()->create(['question' => 'Second general?', 'group' => 'General', 'sort_order' => 3]);
        Faq::factory()->create(['question' => 'First general?', 'group' => 'General', 'sort_order' => 1]);
        Faq::factory()->create(['question' => 'About fees?', 'group' => 'Working with ALCO', 'sort_order' => 2]);
        Faq::factory()->create(['question' => 'Hidden?', 'is_published' => false]);

        $this->get('/faq')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/Faq')
            ->has('groups', 2)
            ->where('groups.0.name', 'General')
            ->where('groups.0.items.0.question', 'First general?')
            ->where('groups.0.items.1.question', 'Second general?')
            ->where('groups.1.name', 'Working with ALCO')
            ->has('groups.1.items', 1)
            ->etc());
    }

    public function test_service_pages_show_their_published_questions(): void
    {
        $service = Service::factory()->create(['slug' => 'bookkeeping']);
        $service->publish();
        $related = Faq::factory()->create(['question' => 'How often do you reconcile?']);
        $related->services()->attach($service);
        Faq::factory()->create(['is_published' => false])->services()->attach($service);
        Faq::factory()->create(['question' => 'Unrelated?']);

        $this->get('/services/bookkeeping')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('faqs', 1)
            ->where('faqs.0.question', 'How often do you reconcile?')
            ->etc());
    }

    public function test_homepage_faq_block_shows_selected_published_questions_in_order(): void
    {
        $first = Faq::factory()->create(['question' => 'Picked first?']);
        $second = Faq::factory()->create(['question' => 'Picked second?']);
        $draft = Faq::factory()->create(['is_published' => false]);

        $home = Page::factory()->create([
            'slug' => 'home',
            'draft_data' => [
                'hero_line_one' => 'Heading',
                'blocks' => [[
                    'type' => 'faq',
                    'data' => [
                        'eyebrow' => 'Questions',
                        'title' => 'Common questions',
                        'faq_ids' => [(string) $second->id, (string) $draft->id, (string) $first->id],
                    ],
                ]],
            ],
        ]);
        $home->publish();

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('home.blocks.0.data.items', 2)
            ->where('home.blocks.0.data.items.0.question', 'Picked second?')
            ->where('home.blocks.0.data.items.1.question', 'Picked first?')
            ->missing('home.blocks.0.data.faq_ids')
            ->etc());

        // Editing an FAQ updates the homepage without republishing it.
        $first->update(['question' => 'Picked first, reworded?']);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('home.blocks.0.data.items.1.question', 'Picked first, reworded?')
            ->etc());
    }

    public function test_faq_answers_are_shown_as_safe_rich_text(): void
    {
        Faq::factory()->create(['question' => 'Rich?', 'group' => 'General', 'sort_order' => 1, 'answer' => '<p>See <a href="https://example.com">this</a></p><script>alert(1)</script>']);
        Faq::factory()->create(['question' => 'Plain?', 'group' => 'General', 'sort_order' => 2, 'answer' => "One.\n\nTwo."]);

        $this->get('/faq')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('groups.0.items', fn ($items) => str_contains($items[0]['answer_html'], '<a href="https://example.com"')
                && ! str_contains($items[0]['answer_html'], '<script')
                && str_contains($items[1]['answer_html'], '<p>One.</p><p>Two.</p>'))
            ->etc());
    }

    public function test_service_body_is_shown_as_safe_rich_text(): void
    {
        $service = Service::factory()->create(['slug' => 'rich-service', 'draft_data' => [
            'summary' => 'Summary',
            'body' => '<h3>Scope</h3><ul><li>Books</li></ul><script>alert(1)</script>',
        ]]);
        $service->publish();

        $this->get('/services/rich-service')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('service.body_html', fn ($html) => str_contains($html, '<li>Books</li>') && ! str_contains($html, '<script'))
            ->etc());
    }

    public function test_inline_homepage_questions_move_into_the_library(): void
    {
        $home = Page::factory()->create([
            'slug' => 'home',
            'draft_data' => [
                'hero_line_one' => 'Heading',
                'blocks' => [[
                    'type' => 'faq',
                    'data' => [
                        'eyebrow' => 'Questions',
                        'title' => 'Common questions',
                        'items' => [
                            ['question' => 'Where is ALCO based?', 'answer' => 'Ebute-Metta, Lagos.'],
                            ['question' => '', 'answer' => 'Skipped because it has no question.'],
                        ],
                    ],
                ]],
            ],
        ]);
        $home->publish();

        $migration = require database_path('migrations/2026_10_14_090100_move_homepage_faqs_into_library.php');
        $migration->up();

        $faq = Faq::sole();
        $this->assertSame('Where is ALCO based?', $faq->question);
        $this->assertTrue($faq->is_published);

        $block = $home->refresh()->draft_data['blocks'][0]['data'];
        $this->assertSame([(string) $faq->id], $block['faq_ids']);
        $this->assertArrayNotHasKey('items', $block);

        // The published snapshot still renders until the homepage is republished.
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('home.blocks.0.data.items.0.question', 'Where is ALCO based?')
            ->etc());
    }

    public function test_admin_can_add_a_question_linked_to_services(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $service = Service::factory()->create();
        Faq::factory()->create(['sort_order' => 7]);

        Livewire::test(CreateFaq::class)
            ->fillForm([
                'question' => 'Do you work with startups?',
                'answer' => 'Yes. The Essentials package is designed for them.',
                'group' => 'Working with ALCO',
                'services' => [$service->id],
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $faq = Faq::where('question', 'Do you work with startups?')->sole();
        $this->assertSame(8, $faq->sort_order);
        $this->assertTrue($faq->services->contains($service));
    }
}
