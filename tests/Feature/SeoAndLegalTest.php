<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class SeoAndLegalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.url' => 'https://alcosolutions.ng']);
    }

    private function legalPage(string $slug, bool $published): Page
    {
        // The migration already creates unpublished drafts for these slugs.
        Page::where('slug', $slug)->delete();

        $page = Page::factory()->create([
            'slug' => $slug,
            'title' => ucfirst($slug).' notice',
            'template_key' => 'legal',
            'draft_data' => [
                'hero_eyebrow' => 'Legal',
                'hero_description' => 'Intro.',
                'effective_date' => '1 November 2026',
                'sections' => [['heading' => 'What we collect', 'body' => "First paragraph.\n\nSecond paragraph."]],
            ],
        ]);

        if ($published) {
            $page->publish();
        }

        return $page;
    }

    public function test_legal_pages_stay_hidden_until_published_and_then_appear_in_the_footer(): void
    {
        $this->get('/privacy')->assertNotFound();
        $this->legalPage('privacy', false);

        $this->get('/privacy')->assertNotFound();
        $this->get('/terms')->assertNotFound();
        $this->get('/contact')->assertInertia(fn (Assert $page) => $page->where('legalLinks', [])->etc());
        $this->get('/sitemap.xml')->assertDontSee('/privacy');

        Page::where('slug', 'privacy')->sole()->publish();

        $this->get('/privacy')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/Legal')
            ->where('legal.title', 'Privacy notice')
            ->where('legal.effective_date', '1 November 2026')
            ->where('legal.sections.0.heading', 'What we collect')
            ->etc());

        $this->get('/contact')->assertInertia(fn (Assert $page) => $page
            ->where('legalLinks', [['label' => 'Privacy notice', 'href' => '/privacy']])
            ->etc());
        $this->get('/sitemap.xml')->assertSee('https://alcosolutions.ng/privacy');
    }

    public function test_legal_section_text_is_shown_as_safe_rich_text(): void
    {
        $page = $this->legalPage('privacy', false);
        $page->update(['draft_data' => [
            ...$page->draft_data,
            'sections' => [
                ['heading' => 'Rich', 'body' => '<p>Hello <strong>world</strong></p><script>alert(1)</script><ul><li>One</li></ul>'],
                ['heading' => 'Legacy', 'body' => "First paragraph.\n\nSecond paragraph & more."],
            ],
        ]]);
        $page->publish();

        $this->get('/privacy')->assertOk()->assertInertia(function (Assert $page) {
            $page->where('legal.sections.0.body_html', fn ($html) => str_contains($html, '<strong>world</strong>')
                && str_contains($html, '<li>One</li>')
                && ! str_contains($html, '<script'));
            $page->where('legal.sections.1.body_html', fn ($html) => str_contains($html, '<p>First paragraph.</p>')
                && str_contains($html, '<p>Second paragraph &amp; more.</p>'));
            $page->etc();
        });
    }

    public function test_a_homepage_or_about_page_cannot_be_served_as_legal_text(): void
    {
        Page::where('slug', 'privacy')->update(['template_key' => 'about']);
        Page::where('slug', 'privacy')->sole()->publish();

        $this->get('/privacy')->assertNotFound();
    }

    public function test_admin_creates_a_legal_page_with_text_sections(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        Page::where('slug', 'terms')->delete();

        Livewire::test(CreatePage::class)
            ->fillForm(['slug' => 'terms'])
            ->assertSchemaStateSet(['template_key' => 'legal'])
            ->fillForm([
                'title' => 'Terms of use',
                'draft_data' => [
                    'hero_eyebrow' => 'Legal',
                    'hero_line_one' => 'Terms of use',
                    'hero_description' => 'The terms for using this website.',
                    'effective_date' => '1 November 2026',
                    'sections' => [['heading' => 'Using this website', 'body' => 'General information only.']],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $page = Page::where('slug', 'terms')->sole();
        $this->assertSame('legal', $page->template_key);
        $this->assertSame('Using this website', $page->draft_data['sections'][0]['heading']);
    }

    public function test_the_sitemap_lists_only_public_indexable_pages(): void
    {
        Page::factory()->create(['slug' => 'home'])->publish();
        Service::factory()->create(['slug' => 'bookkeeping'])->publish();
        Service::factory()->create(['slug' => 'draft-service']);
        Post::create([
            'slug' => 'vat-basics',
            'title' => 'VAT basics',
            'draft_data' => ['excerpt' => 'x', 'body' => 'y'],
        ])->forceFill(['publication_status' => 'published', 'published_data' => ['title' => 'VAT basics'], 'published_at' => now()])->save();
        Post::create(['slug' => 'unpublished-post', 'title' => 'Draft', 'draft_data' => ['excerpt' => 'x', 'body' => 'y']]);

        $response = $this->get('/sitemap.xml')->assertOk();
        $this->assertStringContainsString('application/xml', (string) $response->headers->get('Content-Type'));

        foreach (['https://alcosolutions.ng/', '/about', '/services', '/faq', '/contact', '/insights', '/tools', '/tools/vat', '/services/bookkeeping', '/insights/vat-basics'] as $path) {
            $response->assertSee(str_starts_with($path, 'http') ? $path : "https://alcosolutions.ng{$path}", false);
        }

        foreach (['/services/draft-service', '/insights/unpublished-post', '/book-consultation', '/admin'] as $path) {
            $response->assertDontSee("https://alcosolutions.ng{$path}", false);
        }
    }

    public function test_robots_blocks_crawling_outside_production_and_points_to_the_sitemap_in_production(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /', false)->assertDontSee('Sitemap:');

        $this->app['env'] = 'production';

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin', false)
            ->assertSee('Sitemap: https://alcosolutions.ng/sitemap.xml', false);
    }

    public function test_each_page_names_one_canonical_address_without_tracking_parameters(): void
    {
        $this->get('/services?utm_source=newsletter&package=x')
            ->assertInertia(fn (Assert $page) => $page->where('canonical', 'https://alcosolutions.ng/services')->etc());

        $this->get('/')->assertInertia(fn (Assert $page) => $page->where('canonical', 'https://alcosolutions.ng/')->etc());

        $this->get('/insights?page=2&q=vat')
            ->assertInertia(fn (Assert $page) => $page->where('canonical', 'https://alcosolutions.ng/insights?page=2')->etc());
    }

    public function test_pages_describe_the_business_with_structured_data_from_site_settings(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<script type="application/ld+json">', $html);
        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $match);
        $graph = json_decode($match[1], true)['@graph'];

        $organization = $graph[0];
        $this->assertSame('AccountingService', $organization['@type']);
        $this->assertSame('ALCO Associate Solutions', $organization['name']);
        $this->assertSame('alcosolutionsng@gmail.com', $organization['email']);
        $this->assertSame('+2348140824230', $organization['telephone']);
        $this->assertSame('NG', $organization['address']['addressCountry']);
        $this->assertSame('Ebute-Metta', $organization['address']['addressLocality']);
        $this->assertArrayNotHasKey('sameAs', $organization, 'No unverified profile links are listed.');
        $this->assertArrayNotHasKey('aggregateRating', $organization);
        $this->assertSame('WebSite', $graph[1]['@type']);
    }

    public function test_public_pages_render_social_previews_in_the_initial_html(): void
    {
        $home = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<meta property="og:title" content="ALCO Associate Solutions">', $home);
        $this->assertStringContainsString('<meta property="og:site_name" content="ALCO Associate Solutions">', $home);
        $this->assertStringContainsString('<meta property="og:url" content="https://alcosolutions.ng/">', $home);
        $this->assertStringContainsString('<meta property="og:image" content="https://alcosolutions.ng/images/alco-social-share.png">', $home);
        $this->assertStringContainsString('<meta property="og:image:width" content="1200">', $home);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $home);
        $this->assertFileExists(public_path('images/alco-social-share.png'));

        $contact = $this->get('/contact?utm_source=whatsapp')->assertOk()->getContent();
        $this->assertStringContainsString('<meta property="og:title" content="Contact us | ALCO Associate Solutions">', $contact);
        $this->assertStringContainsString('<meta property="og:url" content="https://alcosolutions.ng/contact">', $contact);

        $this->get('/login')->assertOk()->assertDontSee('og:image', false);
    }

    public function test_article_social_preview_uses_published_title_and_description(): void
    {
        Post::create(['slug' => 'vat-basics', 'title' => 'VAT basics', 'draft_data' => ['excerpt' => 'Draft text', 'body' => '<p>Body</p>']])
            ->forceFill([
                'publication_status' => 'published',
                'published_data' => ['title' => 'VAT basics', 'excerpt' => 'Clear VAT guidance for business owners.', 'body' => '<p>Body</p>'],
                'published_at' => now(),
            ])->save();

        $html = $this->get('/insights/vat-basics')->assertOk()->getContent();

        $this->assertStringContainsString('<meta property="og:type" content="article">', $html);
        $this->assertStringContainsString('<meta property="og:title" content="VAT basics | ALCO Associate Solutions">', $html);
        $this->assertStringContainsString('<meta property="og:description" content="Clear VAT guidance for business owners.">', $html);
    }

    public function test_service_pages_add_a_breadcrumb_trail_and_articles_a_blog_posting(): void
    {
        Service::factory()->create(['slug' => 'bookkeeping', 'title' => 'Bookkeeping'])->publish();

        $service = $this->graphFor('/services/bookkeeping');
        $trail = collect($service)->firstWhere('@type', 'BreadcrumbList');
        $this->assertSame(['Home', 'Services', 'Bookkeeping'], array_column($trail['itemListElement'], 'name'));
        $this->assertSame('https://alcosolutions.ng/services/bookkeeping', $trail['itemListElement'][2]['item']);

        Post::create([
            'slug' => 'vat-basics',
            'title' => 'VAT basics',
            'draft_data' => ['excerpt' => 'Plain VAT terms.', 'body' => '<p>Body</p>'],
        ])->forceFill([
            'publication_status' => 'published',
            'published_data' => ['title' => 'VAT basics', 'excerpt' => 'Plain VAT terms.', 'body' => '<p>Body</p>', 'author_name' => 'ALCO Team'],
            'published_at' => '2026-10-01 09:00:00',
        ])->save();

        $article = $this->graphFor('/insights/vat-basics');
        $posting = collect($article)->firstWhere('@type', 'BlogPosting');
        $this->assertSame('VAT basics', $posting['headline']);
        $this->assertSame('ALCO Team', $posting['author']['name']);
        $this->assertSame('https://alcosolutions.ng/insights/vat-basics', $posting['url']);
        $this->assertStringStartsWith('2026-10-01', $posting['datePublished']);
        $this->assertNotNull(collect($article)->firstWhere('@type', 'BreadcrumbList'));
    }

    public function test_structured_data_cannot_break_out_of_its_script_tag(): void
    {
        Service::factory()->create(['slug' => 'x', 'title' => '</script><script>alert(1)</script>'])->publish();

        $html = $this->get('/services/x')->assertOk()->getContent();

        $this->assertStringNotContainsString('</script><script>alert(1)', $html);
    }

    public function test_sign_in_pages_do_not_carry_business_structured_data(): void
    {
        $this->get('/login')->assertOk()->assertDontSee('application/ld+json', false);
    }

    /** @return list<array<string, mixed>> */
    private function graphFor(string $path): array
    {
        $html = $this->get($path)->assertOk()->getContent();
        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $match);

        return json_decode($match[1], true)['@graph'];
    }
}
