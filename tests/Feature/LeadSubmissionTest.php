<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Models\EngagementPackage;
use App\Models\Lead;
use App\Models\Service;
use App\Notifications\NewLeadReceived;
use App\Support\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
use Tests\TestCase;

class LeadSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /** @return array<string, string> */
    private function contactForm(array $overrides = []): array
    {
        return [
            'name' => 'Amaka Obi',
            'email' => 'Amaka@Example.com',
            'phone' => '',
            'message' => 'I need help organising my shop records.',
            'consent' => '1',
            ...$overrides,
        ];
    }

    public function test_contact_enquiry_is_saved_with_its_source_and_staff_are_emailed(): void
    {
        Notification::fake();
        $service = Service::factory()->create(['slug' => 'bookkeeping', 'title' => 'Bookkeeping']);
        $service->publish();

        $this->get('/?utm_source=google&utm_medium=cpc&email=leaked@example.com', ['Referer' => 'https://www.google.com/search?q=alco'])->assertOk();
        $this->get('/contact')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/Contact')
            ->where('services.0.title', 'Bookkeeping')
            ->where('formsEnabled', true)
            ->where('turnstileSiteKey', null)
            ->etc());

        $response = $this->from('/contact')
            ->post('/contact', $this->contactForm(['service_id' => (string) $service->id]))
            ->assertRedirect('/contact')
            ->assertSessionHasNoErrors();

        $lead = Lead::sole();
        $response->assertInertiaFlash('leadSubmitted.reference', $lead->reference);

        $this->assertSame(LeadType::Contact, $lead->type);
        $this->assertSame(LeadStatus::New, $lead->status);
        $this->assertSame('amaka@example.com', $lead->email);
        $this->assertNull($lead->phone);
        $this->assertSame('Bookkeeping', $lead->service_title);
        $this->assertSame('/contact', $lead->source_path);
        $this->assertNotNull($lead->consented_at);
        $this->assertStringContainsString('respond to my enquiry', $lead->consent_text);
        $this->assertSame([
            'landing_page' => '/',
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'referrer' => 'www.google.com',
        ], $lead->attribution);
        $this->assertMatchesRegularExpression('/^ENQ-[A-Z0-9]{6}$/', $lead->reference);
        $this->assertSame('submitted', $lead->activities()->sole()->type);

        Notification::assertSentTo(
            new AnonymousNotifiable,
            NewLeadReceived::class,
            fn (NewLeadReceived $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notification->lead->is($lead)
                && $notifiable->routes['mail'] === ['info@alcosolutions.ng'],
        );
    }

    public function test_consultation_keeps_the_chosen_package_as_it_was_shown(): void
    {
        Notification::fake();
        $essentials = EngagementPackage::factory()->create(['slug' => 'essentials', 'title' => 'Essentials', 'sort_order' => 1]);
        $essentials->publish();
        $growth = EngagementPackage::factory()->create([
            'slug' => 'growth',
            'title' => 'Growth',
            'sort_order' => 2,
            'parent_id' => $essentials->id,
            'draft_data' => [
                'summary' => 'Performance and financial health',
                'audience' => 'Scaling companies.',
                'inclusions' => [['label' => 'Cash-flow planning']],
            ],
        ]);
        $growth->publish();

        $this->get('/book-consultation?package=growth')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/Consultation')
            ->where('selectedPackage.slug', 'growth')
            ->has('contactMethods')
            ->etc());

        $this->from('/book-consultation?package=growth')->post('/book-consultation', [
            'name' => 'Tunde Bello',
            'phone' => '+234 814 082 4230',
            'package' => 'growth',
            'preferred_contact_method' => 'whatsapp',
            'availability' => 'Weekday mornings',
            'consent' => '1',
        ])->assertRedirect('/book-consultation')->assertSessionHasNoErrors();

        $lead = Lead::sole();
        $this->assertSame(LeadType::Consultation, $lead->type);
        $this->assertTrue($lead->package?->is($growth));
        $this->assertSame([
            'slug' => 'growth',
            'name' => 'Growth',
            'summary' => 'Performance and financial health',
            'includes' => 'Essentials',
            'inclusions' => ['Cash-flow planning'],
        ], $lead->package_snapshot);
        $this->assertSame('whatsapp', $lead->preferred_contact_method);
        $this->assertNull($lead->message);

        // Later package edits do not rewrite what the visitor chose.
        $growth->update(['title' => 'Growth Plus']);
        $growth->publish();
        $this->assertSame('Growth', $lead->refresh()->packageLabel());
    }

    public function test_tailored_requests_and_unavailable_packages(): void
    {
        Notification::fake();
        EngagementPackage::factory()->create(['slug' => 'strategic', 'is_selectable' => false])->publish();
        EngagementPackage::factory()->create(['slug' => 'draft-only']);

        foreach (['strategic', 'draft-only', 'unknown'] as $slug) {
            $this->from('/book-consultation')->post('/book-consultation', [
                'name' => 'Ngozi',
                'email' => 'ngozi@example.com',
                'package' => $slug,
                'consent' => '1',
            ])->assertSessionHasErrors('package');
        }

        $this->assertDatabaseCount('leads', 0);

        $this->from('/book-consultation')->post('/book-consultation', [
            'name' => 'Ngozi',
            'email' => 'ngozi@example.com',
            'package' => 'tailored',
            'consent' => '1',
        ])->assertSessionHasNoErrors();

        $lead = Lead::sole();
        $this->assertTrue($lead->wants_tailored);
        $this->assertNull($lead->package_snapshot);
        $this->assertSame('Tailored engagement', $lead->packageLabel());
    }

    public function test_visitors_must_give_a_way_to_reply_and_consent(): void
    {
        $this->from('/contact')->post('/contact', $this->contactForm([
            'email' => '',
            'phone' => '',
            'consent' => '',
            'message' => '',
        ]))->assertSessionHasErrors(['email', 'phone', 'consent', 'message']);

        $this->from('/contact')->post('/contact', $this->contactForm([
            'email' => 'not-an-email',
            'phone' => 'call me maybe',
        ]))->assertSessionHasErrors(['email', 'phone']);

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_bots_that_fill_the_hidden_field_are_quietly_ignored(): void
    {
        Notification::fake();

        $this->from('/contact')
            ->post('/contact', $this->contactForm(['website' => 'https://spam.example']))
            ->assertRedirect('/contact')
            ->assertInertiaFlash('leadSubmitted.reference', null);

        $this->assertDatabaseCount('leads', 0);
        Notification::assertNothingSent();
    }

    public function test_a_double_submission_creates_one_enquiry(): void
    {
        Notification::fake();

        $this->from('/contact')->post('/contact', $this->contactForm());
        $second = $this->from('/contact')->post('/contact', $this->contactForm());

        $lead = Lead::sole();
        $second->assertInertiaFlash('leadSubmitted.reference', $lead->reference);
        Notification::assertSentTimes(NewLeadReceived::class, 1);
    }

    public function test_bursts_of_submissions_are_rate_limited(): void
    {
        Notification::fake();

        for ($attempt = 1; $attempt <= 8; $attempt++) {
            $this->from('/contact')->post('/contact', $this->contactForm(['message' => "Question number {$attempt}"]));
        }

        $this->from('/contact')
            ->post('/contact', $this->contactForm(['message' => 'One too many']))
            ->assertRedirect('/contact')
            ->assertSessionHasErrors('form');

        $this->assertDatabaseCount('leads', 8);
    }

    public function test_paused_forms_point_visitors_to_phone_and_email(): void
    {
        SiteSettings::put('forms', ['forms_enabled' => false, 'notification_emails' => ['info@alcosolutions.ng']]);

        $this->get('/contact')->assertInertia(fn (Assert $page) => $page->where('formsEnabled', false)->etc());

        $this->from('/contact')
            ->post('/contact', $this->contactForm())
            ->assertSessionHasErrors('form');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_turnstile_is_checked_on_the_server_when_configured(): void
    {
        Notification::fake();
        config(['services.turnstile.site_key' => 'site-key', 'services.turnstile.secret_key' => 'secret']);
        Http::fake([
            'challenges.cloudflare.com/*' => Http::sequence()
                ->push(['success' => false])
                ->push(['success' => true]),
        ]);

        $this->get('/contact')->assertInertia(fn (Assert $page) => $page->where('turnstileSiteKey', 'site-key')->etc());

        $this->from('/contact')->post('/contact', $this->contactForm())->assertSessionHasErrors('cf-turnstile-response');
        $this->from('/contact')->post('/contact', $this->contactForm(['cf-turnstile-response' => 'bad-token']))->assertSessionHasErrors('cf-turnstile-response');
        $this->from('/contact')->post('/contact', $this->contactForm(['cf-turnstile-response' => 'good-token']))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('leads', 1);
        Http::assertSent(fn ($request): bool => str_contains($request->url(), 'challenges.cloudflare.com')
            && $request['secret'] === 'secret'
            && $request['response'] === 'good-token');
    }

    public function test_a_failed_staff_email_never_loses_the_enquiry(): void
    {
        Mail::shouldReceive('mailer')->andThrow(new RuntimeException('SMTP unavailable'));
        $this->withoutExceptionHandling();

        $this->from('/contact')
            ->post('/contact', $this->contactForm())
            ->assertRedirect('/contact');

        $lead = Lead::sole();
        $this->assertSame('failed', $lead->notification_status);
        $this->assertTrue($lead->activities()->where('type', 'notification')->exists());
    }

    public function test_sent_emails_are_recorded_on_the_enquiry(): void
    {
        $this->from('/contact')->post('/contact', $this->contactForm())->assertSessionHasNoErrors();

        $lead = Lead::sole();
        $this->assertSame('sent', $lead->notification_status);
        $this->assertNotNull($lead->notified_at);
    }

    public function test_without_staff_addresses_the_enquiry_is_still_saved(): void
    {
        Notification::fake();
        SiteSettings::put('forms', ['forms_enabled' => true, 'notification_emails' => []]);

        $this->from('/contact')->post('/contact', $this->contactForm())->assertSessionHasNoErrors();

        $this->assertSame('skipped', Lead::sole()->notification_status);
        Notification::assertNothingSent();
    }
}
