<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Starting drafts for the legal pages. They are created unpublished, so
 * nothing appears on the site until ALCO approves the wording (and takes
 * legal advice where needed) and publishes each page. Square brackets mark
 * details that only ALCO can confirm.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->pages() as $slug => $page) {
            if (DB::table('pages')->where('slug', $slug)->exists()) {
                continue;
            }

            DB::table('pages')->insert([
                'slug' => $slug,
                'title' => $page['title'],
                'template_key' => 'legal',
                'draft_data' => json_encode([
                    'hero_eyebrow' => 'Legal',
                    'hero_line_one' => $page['title'],
                    'hero_description' => $page['intro'],
                    'seo_description' => $page['seo'],
                    'effective_date' => '[Date to be confirmed on publication]',
                    'sections' => $page['sections'],
                ]),
                'publication_status' => 'draft',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('pages')
            ->whereIn('slug', ['privacy', 'terms', 'calculator-disclaimer'])
            ->whereNull('published_data')
            ->delete();
    }

    /** @return array<string, array{title: string, intro: string, seo: string, sections: list<array{heading: string, body: string}>}> */
    private function pages(): array
    {
        return [
            'privacy' => [
                'title' => 'Privacy notice',
                'intro' => 'How ALCO Associate Solutions handles the personal information you give us through this website.',
                'seo' => 'How ALCO Associate Solutions collects, uses and protects personal information shared through its website.',
                'sections' => [
                    ['heading' => 'Who we are', 'body' => "ALCO Associate Solutions is an Accounting and Auditing Consultancy at 18, Lagos Street, Ebute-Metta, Lagos State, Nigeria.\n\nYou can contact us about this notice using the details on our Contact page. [ALCO to confirm the contact for privacy questions.]"],
                    ['heading' => 'What we collect', 'body' => "When you send an enquiry or consultation request we collect your name, your email address and/or phone number, the service or package you are interested in, your message, how you prefer to be contacted and when you are available.\n\nWe also keep a record of the page you sent the form from and, where available, the campaign tags or referring website that brought you to us. We do not ask for bank details or confidential financial records through our forms, and we ask you not to send them."],
                    ['heading' => 'Why we use it', 'body' => "We use your details to respond to your enquiry, to arrange a consultation if you ask for one, and to keep a record of our conversation. We keep your consent to this use with your enquiry.\n\n[ALCO to confirm whether any other use, such as marketing, is intended. Marketing messages need separate, optional consent.]"],
                    ['heading' => 'Who receives it', 'body' => "Your enquiry is emailed to ALCO staff and stored in our website's administration area. [ALCO to list any service providers that process this information, such as hosting, email delivery and spam protection, including Cloudflare Turnstile.]"],
                    ['heading' => 'How long we keep it', 'body' => '[ALCO to confirm the retention period for enquiries and the process for deleting them.]'],
                    ['heading' => 'Your choices and rights', 'body' => 'You can ask us what information we hold about you, to correct it, or to delete it, by contacting us. [ALCO to confirm the process and to review this section against applicable Nigerian data-protection requirements.]'],
                    ['heading' => 'Cookies and analytics', 'body' => '[ALCO to describe any cookies or analytics used on this website and how consent is handled, once these are decided.]'],
                    ['heading' => 'Changes to this notice', 'body' => 'We may update this notice. The date at the top shows when it was last changed.'],
                ],
            ],
            'terms' => [
                'title' => 'Terms of use',
                'intro' => 'The terms for using the ALCO Associate Solutions website.',
                'seo' => 'Terms for using the ALCO Associate Solutions website.',
                'sections' => [
                    ['heading' => 'Using this website', 'body' => 'This website gives general information about ALCO Associate Solutions and its services. By using it you agree to these terms. [ALCO to review all wording with legal advice.]'],
                    ['heading' => 'General information only', 'body' => 'Content on this website, including articles and any calculators, is general information. It is not accounting, tax, audit or legal advice, and it does not take your circumstances into account. Please speak to us or another qualified professional before acting on it.'],
                    ['heading' => 'Enquiries and packages', 'body' => 'Sending an enquiry, consultation request or package choice does not create an appointment, a contract or a commitment to provide services. ALCO confirms suitability, scope, fees and terms directly with you before any work begins.'],
                    ['heading' => 'Our content', 'body' => '[ALCO to confirm how website content, logos and images may be used, and who owns them.]'],
                    ['heading' => 'Links to other websites', 'body' => 'Our website may link to other websites. We are not responsible for their content.'],
                    ['heading' => 'Limits of liability', 'body' => '[ALCO to confirm the wording with legal advice.]'],
                    ['heading' => 'Governing law and contact', 'body' => '[ALCO to confirm the governing law and how to contact us about these terms.]'],
                ],
            ],
            'calculator-disclaimer' => [
                'title' => 'Calculator disclaimer',
                'intro' => 'Important information about the accounting tools and calculators on this website.',
                'seo' => 'Important information about how to use the ALCO accounting tools and calculators.',
                'sections' => [
                    ['heading' => 'Estimates, not advice', 'body' => 'Results from our tools are estimates based on the figures you enter and the settings shown with each result. They are not a filed tax liability, an accounting or audit conclusion, or an investment recommendation.'],
                    ['heading' => 'Settings and effective dates', 'body' => "Where a tool uses a tax rate or rule, the result shows the settings used, the date they apply to and when they were last reviewed. Rates and rules change, so please check that they match your situation. [ALCO's qualified reviewer must approve each setting before it is shown.]"],
                    ['heading' => 'Your figures stay private', 'body' => '[ALCO to confirm: the tools calculate your result without storing the amounts you enter.]'],
                    ['heading' => 'Talk to a professional', 'body' => 'For a calculation that matters to your business, or to confirm a rate or rule, contact us or another qualified professional.'],
                ],
            ],
        ];
    }
};
