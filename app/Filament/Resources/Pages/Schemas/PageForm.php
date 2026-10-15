<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Resources\HomepageHeroSlides\HomepageHeroSlideResource;
use App\Models\Faq;
use App\Models\Page;
use App\Support\ArticleRichText;
use App\Support\Navigation;
use App\Support\PageBlueprints;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Page identity')
                    ->description('Existing site pages keep their current address. A new page gets a /pages/your-slug address and can be added to Navigation after publication.')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(160)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                if (blank($get('slug')) && filled($state)) {
                                    $slug = PageBlueprints::slugFromTitle($state);
                                    $set('slug', $slug);
                                    $set('template_key', PageBlueprints::templateForSlug($slug));
                                    $set('draft_data.hero_line_one', $state);
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Page address / slug')
                            ->required()
                            ->maxLength(120)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->unique(ignoreRecord: true)
                            ->disabledOn('edit')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('template_key', PageBlueprints::templateForSlug((string) $state)))
                            ->helperText('Existing pages are listed in the table. New page addresses appear at /pages/{slug}. Locked after creation.'),
                        Select::make('starter_template')
                            ->label('Starting layout')
                            ->options(PageBlueprints::STARTERS)
                            ->default('blank')
                            ->dehydrated(false)
                            ->live()
                            ->visible(fn (Get $get): bool => filled($get('slug')) && PageBlueprints::isCustom((string) $get('slug')))
                            ->afterStateUpdated(fn (Get $get, Set $set, ?string $state) => $set('draft_data.blocks', PageBlueprints::starterBlocks((string) $state, (string) $get('title'))))
                            ->helperText('Choose a simple layout, then edit or reorder its blocks below. Changing this choice replaces the draft blocks.'),
                        Hidden::make('template_key')->default('standard'),
                    ])
                    ->columns(2),
                Group::make()
                    ->statePath('draft_data')
                    ->schema([
                        Section::make('Draft hero copy')
                            ->description(fn (Get $get): string => self::isHome($get)
                                ? 'Save changes first. The visitor site changes only when you publish the saved draft. The banner images under this text are managed in Website → Homepage slider and go live as soon as a slide is shown.'
                                : 'Save changes first. The visitor site changes only when you publish the saved draft.')
                            ->headerActions([
                                Action::make('manageSlider')
                                    ->label('Manage homepage slider')
                                    ->icon(Heroicon::OutlinedFilm)
                                    ->link()
                                    ->visible(fn (?Page $record): bool => ($record->template_key ?? 'home') === 'home')
                                    ->url(fn (): string => HomepageHeroSlideResource::getUrl('index')),
                            ])
                            ->schema([
                                TextInput::make('hero_eyebrow')
                                    ->label('Small heading')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('hero_line_one')
                                    ->label(fn (Get $get): string => self::isHome($get) ? 'Hero line one' : 'Main heading')
                                    ->required()
                                    ->maxLength(120),
                                TextInput::make('hero_line_two')
                                    ->required()
                                    ->maxLength(120)
                                    ->visible(fn (Get $get): bool => self::isHome($get)),
                                TextInput::make('hero_accent')
                                    ->label('Hero accent (highlighted words)')
                                    ->required()
                                    ->maxLength(120)
                                    ->visible(fn (Get $get): bool => self::isHome($get)),
                                Textarea::make('hero_description')
                                    ->label(fn (Get $get): string => self::isHome($get) ? 'Hero description' : 'Introduction')
                                    ->required()
                                    ->maxLength(400),
                                TextInput::make('hero_tagline')
                                    ->label('Line above the slider')
                                    ->maxLength(120)
                                    ->placeholder('Clarity today. Confidence for what comes next.')
                                    ->helperText('Short line shown just above the homepage slider. Leave empty to hide it.')
                                    ->visible(fn (Get $get): bool => self::isHome($get)),
                                Textarea::make('seo_description')
                                    ->label('Search engine description')
                                    ->maxLength(160),
                            ])
                            ->columns(2),
                        Section::make('Homepage: service section')
                            ->description('These are the fixed sections around the reorderable blocks. Service cards are edited under Website → Services.')
                            ->visible(fn (Get $get): bool => self::isHome($get))
                            ->schema([
                                TextInput::make('services_eyebrow')->label('Small heading')->maxLength(80),
                                TextInput::make('services_title')->label('Section heading')->maxLength(160),
                            ])->columns(2),
                        Section::make('Homepage: Insights and Tools cards')
                            ->visible(fn (Get $get): bool => self::isHome($get))
                            ->schema([
                                TextInput::make('discover_eyebrow')->label('Small heading')->maxLength(80),
                                TextInput::make('discover_title')->label('Section heading')->maxLength(160),
                                Textarea::make('discover_description')->label('Section introduction')->maxLength(400)->columnSpanFull(),
                                TextInput::make('insights_card_title')->label('Insights card title')->maxLength(80),
                                Textarea::make('insights_card_text')->label('Insights card description')->maxLength(300),
                                TextInput::make('tools_card_title')->label('Tools card title')->maxLength(80),
                                Textarea::make('tools_card_text')->label('Tools card description')->maxLength(300),
                            ])->columns(2),
                        Section::make('Final call to action')
                            ->description('Shown at the bottom of Home or About unless a Call to action block is inserted in Page sections.')
                            ->visible(fn (Get $get): bool => self::isHome($get) || self::isAbout($get))
                            ->schema([
                                TextInput::make('cta_eyebrow')->label('Small heading')->maxLength(80)
                                    ->visible(fn (Get $get): bool => self::isHome($get)),
                                TextInput::make('cta_title')->label('Heading')->maxLength(160),
                                Textarea::make('cta_description')->label('Description')->maxLength(400)->columnSpanFull(),
                            ])->columns(2),
                        Section::make('Legal text')
                            ->description('Wording must be approved by ALCO and, where needed, legal advice before this page is published. The page stays hidden from the site until published.')
                            ->visible(fn (Get $get): bool => self::isLegal($get))
                            ->schema([
                                TextInput::make('effective_date')
                                    ->label('Last updated')
                                    ->placeholder('For example: 1 November 2026')
                                    ->maxLength(60),
                                Repeater::make('sections')
                                    ->label('Sections')
                                    ->schema([
                                        TextInput::make('heading')->required()->maxLength(160),
                                        RichEditor::make('body')
                                            ->toolbarButtons([
                                                ['bold', 'italic', 'underline', 'link'],
                                                ['h3'],
                                                ['bulletList', 'orderedList', 'blockquote'],
                                                ['undo', 'redo'],
                                            ])
                                            ->linkProtocols(['https', 'mailto', 'tel'])
                                            ->formatStateUsing(fn (mixed $state): mixed => is_string($state) ? ArticleRichText::asHtml($state) : $state)
                                            ->required()
                                            ->minHeight('12rem')
                                            ->maxLength(20000)
                                            ->helperText('Use bold, links, lists and sub-headings. Pasted content is cleaned before it is displayed on the public website.'),
                                    ])
                                    ->minItems(1)
                                    ->maxItems(30)
                                    ->default([])
                                    ->addActionLabel('Add section')
                                    ->collapsible(),
                            ]),
                        Section::make('Page sections')
                            ->visible(fn (Get $get): bool => self::supportsBlocks($get))
                            ->description(fn (Get $get): string => self::isHome($get)
                                ? 'Reorder the approved content blocks below the service cards.'
                                : 'Add, remove and reorder the sections shown below the page heading.')
                            ->schema([
                                Builder::make('blocks')
                                    ->blocks([
                                        Block::make('services')
                                            ->label('Our services')
                                            ->schema([
                                                Text::make('Lists the published services from Website → Services, each linking to its page.'),
                                                TextInput::make('eyebrow')->required()->maxLength(80),
                                                TextInput::make('title')->required()->maxLength(160),
                                                self::blockBodyEditor(2000),
                                            ]),
                                        Block::make('team')
                                            ->label('Leadership')
                                            ->schema([
                                                Text::make('Lists the published people from Website → Team, with their portrait, role and biography.'),
                                                TextInput::make('eyebrow')->required()->maxLength(80),
                                                TextInput::make('title')->required()->maxLength(160),
                                                self::blockBodyEditor(2000),
                                            ]),
                                        Block::make('packages')
                                            ->label('Engagement packages')
                                            ->schema([
                                                Text::make('Lists the published packages from Website → Packages, with a link to compare them. Hidden while none are published.'),
                                                TextInput::make('eyebrow')->required()->maxLength(80),
                                                TextInput::make('title')->required()->maxLength(160),
                                                self::blockBodyEditor(2000),
                                            ]),
                                        Block::make('intro')
                                            ->label('Introduction')
                                            ->schema([
                                                TextInput::make('eyebrow')->required()->maxLength(80),
                                                TextInput::make('title')->required()->maxLength(160),
                                                self::blockBodyEditor(4000, required: true),
                                            ]),
                                        Block::make('rich_text')
                                            ->label('Text section')
                                            ->schema([
                                                TextInput::make('eyebrow')->label('Small heading')->maxLength(80),
                                                TextInput::make('title')->label('Section heading')->required()->maxLength(160),
                                                RichEditor::make('body')
                                                    ->label('Content')
                                                    ->toolbarButtons([
                                                        ['bold', 'italic', 'underline', 'link'],
                                                        ['h2', 'h3'],
                                                        ['bulletList', 'orderedList', 'blockquote'],
                                                        ['undo', 'redo'],
                                                    ])
                                                    ->linkProtocols(['https', 'mailto', 'tel'])
                                                    ->required()
                                                    ->maxLength(20000)
                                                    ->helperText('Formatted text is cleaned before it appears on the public page.'),
                                            ]),
                                        Block::make('audience')
                                            ->label('Who we help')
                                            ->schema([
                                                TextInput::make('eyebrow')->required()->maxLength(80),
                                                TextInput::make('title')->required()->maxLength(160),
                                                self::blockBodyEditor(4000, required: true),
                                                Repeater::make('items')
                                                    ->schema([
                                                        TextInput::make('label')->required()->maxLength(80),
                                                        Textarea::make('detail')->required()->maxLength(300),
                                                    ])
                                                    ->minItems(1)
                                                    ->maxItems(6),
                                            ]),
                                        Block::make('process')
                                            ->label('How we work')
                                            ->schema([
                                                TextInput::make('eyebrow')->required()->maxLength(80),
                                                TextInput::make('title')->required()->maxLength(160),
                                                Repeater::make('steps')
                                                    ->schema([
                                                        TextInput::make('title')->required()->maxLength(100),
                                                        Textarea::make('detail')->required()->maxLength(300),
                                                    ])
                                                    ->minItems(1)
                                                    ->maxItems(6),
                                            ]),
                                        Block::make('testimonial')
                                            ->label('Testimonial')
                                            ->schema([
                                                Text::make('Add a real client quote only after you have permission to publish it. No testimonial is created automatically.'),
                                                TextInput::make('eyebrow')->label('Small heading')->maxLength(80),
                                                TextInput::make('title')->label('Section heading')->required()->maxLength(160),
                                                Textarea::make('quote')->label('Client quote')->required()->maxLength(1200)->rows(5),
                                                TextInput::make('author')->label('Client name or approved attribution')->required()->maxLength(120),
                                                TextInput::make('context')->label('Role or business (optional)')->maxLength(160),
                                            ]),
                                        Block::make('highlights')
                                            ->label('Highlights grid')
                                            ->schema([
                                                TextInput::make('eyebrow')->label('Small heading')->maxLength(80),
                                                TextInput::make('title')->label('Section heading')->required()->maxLength(160),
                                                Repeater::make('items')
                                                    ->label('Highlights')
                                                    ->schema([
                                                        TextInput::make('label')->label('Heading')->required()->maxLength(100),
                                                        Textarea::make('detail')->label('Description')->required()->maxLength(350)->rows(3),
                                                    ])
                                                    ->minItems(2)
                                                    ->maxItems(6)
                                                    ->defaultItems(3)
                                                    ->addActionLabel('Add highlight')
                                                    ->collapsible(),
                                            ]),
                                        Block::make('resource_links')
                                            ->label('Resource links')
                                            ->schema([
                                                TextInput::make('eyebrow')->label('Small heading')->maxLength(80),
                                                TextInput::make('title')->label('Section heading')->required()->maxLength(160),
                                                Repeater::make('items')
                                                    ->label('Links')
                                                    ->schema([
                                                        TextInput::make('label')->label('Link title')->required()->maxLength(100),
                                                        Textarea::make('detail')->label('Short description')->maxLength(250)->rows(3),
                                                        TextInput::make('href')
                                                            ->label('Destination')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                                                if (! is_string($value) || ! Navigation::isSafeUrl($value)) {
                                                                    $fail('Use a site path such as /insights or an https:// address.');
                                                                }
                                                            })
                                                            ->helperText('Use a site path, such as /tools or /pages/your-page, or a full https:// link.'),
                                                    ])
                                                    ->minItems(1)
                                                    ->maxItems(6)
                                                    ->defaultItems(2)
                                                    ->addActionLabel('Add link')
                                                    ->collapsible(),
                                            ]),
                                        Block::make('cta')
                                            ->label('Call to action — Let’s talk')
                                            ->schema([
                                                Text::make('Uses the same panel design as the existing “Let’s talk” section. On Home and About, this block replaces the fixed bottom panel.'),
                                                TextInput::make('eyebrow')->label('Small heading')->default('Let’s talk')->maxLength(80),
                                                TextInput::make('title')->label('Heading')->default('Ready to bring more clarity to your business?')->required()->maxLength(160),
                                                Textarea::make('description')->default('Tell us what you need help with and begin a conversation with ALCO.')->maxLength(400)->rows(3),
                                                TextInput::make('button_label')->label('Button text')->default('Book a Consultation')->required()->maxLength(60),
                                                TextInput::make('button_href')
                                                    ->label('Button destination')
                                                    ->default('/book-consultation')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                                        if (! is_string($value) || ! str_starts_with($value, '/') || ! Navigation::isSafeUrl($value)) {
                                                            $fail('Use a site path such as /contact, /book-consultation or /pages/your-page.');
                                                        }
                                                    })
                                                    ->helperText('Use an internal site path, for example /contact or /book-consultation.'),
                                            ]),
                                        Block::make('faq')
                                            ->label('Frequently asked questions')
                                            ->schema([
                                                TextInput::make('eyebrow')->required()->maxLength(80),
                                                TextInput::make('title')->required()->maxLength(160),
                                                Select::make('faq_ids')
                                                    ->label('Questions')
                                                    ->multiple()
                                                    ->required()
                                                    ->maxItems(10)
                                                    ->options(fn (): array => Faq::query()
                                                        ->orderBy('sort_order')
                                                        ->get()
                                                        ->mapWithKeys(fn (Faq $faq): array => [
                                                            $faq->id => $faq->is_published ? $faq->question : "{$faq->question} (not published)",
                                                        ])
                                                        ->all())
                                                    ->searchable()
                                                    ->helperText('Shown in the order selected. Only published questions appear on the site; manage them in Website → FAQs.'),
                                            ]),
                                    ])
                                    ->default([]),
                            ]),
                    ]),
            ]);
    }

    private static function isLegal(Get $get): bool
    {
        return ($get('../template_key') ?? $get('template_key')) === 'legal';
    }

    private static function blockBodyEditor(int $maxLength, bool $required = false): RichEditor
    {
        $editor = RichEditor::make('body')
            ->label('Body')
            ->toolbarButtons([
                ['bold', 'italic', 'underline', 'link'],
                ['h3'],
                ['bulletList', 'orderedList', 'blockquote'],
                ['undo', 'redo'],
            ])
            ->linkProtocols(['https', 'mailto', 'tel'])
            ->formatStateUsing(fn (mixed $state): mixed => is_string($state) ? ArticleRichText::asHtml($state) : $state)
            ->minHeight('10rem')
            ->maxLength($maxLength)
            ->helperText('Existing plain text is shown as a paragraph. Formatting is cleaned before it appears on the website.');

        return $required ? $editor->required() : $editor;
    }

    private static function isAbout(Get $get): bool
    {
        return ($get('../template_key') ?? $get('template_key')) === 'about';
    }

    private static function supportsBlocks(Get $get): bool
    {
        $slug = $get('../slug') ?? $get('slug');

        return is_string($slug) && ($slug === 'home' || $slug === 'about' || PageBlueprints::isCustom($slug));
    }

    /** Fields inside draft_data read the template from the page level. */
    private static function isHome(Get $get): bool
    {
        $template = $get('../template_key') ?? $get('template_key');

        return ! is_string($template) || $template === 'home';
    }
}
