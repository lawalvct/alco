<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSiteSettings;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_public_pages_use_owner_confirmed_defaults_before_settings_are_saved(): void
    {
        $this->get('/contact')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('site.name', 'ALCO Associate Solutions')
            ->where('site.emails', ['alcosolutionsng@gmail.com', 'info@alcosolutions.ng'])
            ->where('site.address.summary', 'Ebute-Metta, Lagos, Nigeria')
            ->where('site.phones.0.tel', '+2348140824230')
            ->where('site.phones.0.display', '+234 814 082 4230')
            ->where('site.phones.0.whatsapp', null)
            ->where('site.whatsappWidget.number', '+2348140824230')
            ->where('site.whatsappWidget.url', 'https://wa.me/2348140824230')
            ->where('site.social', [])
            ->etc());
    }

    public function test_admin_can_update_settings_and_the_site_reflects_them(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $state = SiteSettings::DEFAULTS;
        $state['contact']['emails'] = ['info@alcosolutions.ng', 'alcosolutionsng@gmail.com'];
        $state['contact']['phones'] = [
            ['number' => '+2348140824230', 'whatsapp' => true],
        ];
        $state['contact']['whatsapp_widget_number'] = '+2348148484955';
        $state['social']['linkedin_url'] = 'https://www.linkedin.com/company/alco-solutions-ng';
        $state['homepage']['hero_interval_seconds'] = 9;

        Livewire::test(ManageSiteSettings::class)
            ->fillForm($state)
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertSame($admin->id, SiteSetting::where('group', 'contact')->value('updated_by'));

        $this->get('/contact')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('site.emails', ['info@alcosolutions.ng', 'alcosolutionsng@gmail.com'])
            ->has('site.phones', 1)
            ->where('site.phones.0.whatsapp', 'https://wa.me/2348140824230')
            ->where('site.whatsappWidget.url', 'https://wa.me/2348148484955')
            ->where('site.social.0.label', 'LinkedIn')
            ->where('site.social.0.url', 'https://www.linkedin.com/company/alco-solutions-ng')
            ->etc());

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('heroSlider.interval_seconds', 9)
            ->etc());
    }

    public function test_settings_reject_insecure_links_bad_phone_numbers_and_fast_autoplay(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $state = SiteSettings::DEFAULTS;
        $state['social']['facebook_url'] = 'http://facebook.com/alco';
        $state['contact']['phones'] = [['number' => '0814 082 4230', 'whatsapp' => false]];
        $state['contact']['whatsapp_widget_number'] = '0814 082 4230';
        $state['homepage']['hero_interval_seconds'] = 3;

        Livewire::test(ManageSiteSettings::class)
            ->fillForm($state)
            ->call('save')
            ->assertHasFormErrors([
                'social.facebook_url',
                'contact.whatsapp_widget_number',
                'homepage.hero_interval_seconds',
            ]);

        $this->assertDatabaseCount('site_settings', 0);
    }

    public function test_clearing_the_widget_number_hides_the_chat_button_from_public_pages(): void
    {
        SiteSettings::put('contact', [...SiteSettings::DEFAULTS['contact'], 'whatsapp_widget_number' => null]);

        $this->get('/contact')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('site.whatsappWidget', null)
            ->etc());
    }

    public function test_new_setting_defaults_apply_even_when_saved_settings_are_cached(): void
    {
        SiteSettings::put('contact', [...SiteSettings::DEFAULTS['contact'], 'emails' => ['hello@alcosolutions.ng']]);
        SiteSettings::all();

        // A group added in a later release has no saved row yet but must still work.
        $this->assertSame(['alcosolutionsng@gmail.com', 'info@alcosolutions.ng'], SiteSettings::notificationEmails());
        $this->assertTrue(SiteSettings::formsEnabled());
        $this->assertSame(['hello@alcosolutions.ng'], SiteSettings::publicPayload()['emails']);
    }

    public function test_saved_single_email_settings_gain_the_gmail_address_first(): void
    {
        SiteSetting::create(['group' => 'contact', 'data' => [...SiteSettings::DEFAULTS['contact'], 'email' => 'info@alcosolutions.ng']]);
        SiteSetting::create(['group' => 'forms', 'data' => ['forms_enabled' => true, 'notification_emails' => ['info@alcosolutions.ng', 'alcosolutionsng@gmail.com']]]);

        $migration = require database_path('migrations/2026_10_15_100000_add_gmail_contact_address_to_site_settings.php');
        $migration->up();

        $contact = SiteSetting::where('group', 'contact')->sole()->data;
        $this->assertArrayNotHasKey('email', $contact);
        $this->assertSame(['alcosolutionsng@gmail.com', 'info@alcosolutions.ng'], $contact['emails']);
        $this->assertSame(['alcosolutionsng@gmail.com', 'info@alcosolutions.ng'], SiteSettings::notificationEmails());
        $this->assertSame(['alcosolutionsng@gmail.com', 'info@alcosolutions.ng'], SiteSettings::publicPayload()['emails']);
    }

    public function test_only_admins_can_open_site_settings(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/site-settings')->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/admin/site-settings')->assertOk();
    }
}
