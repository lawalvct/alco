<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Resources\HomepageHeroSlides\HomepageHeroSlideResource;
use App\Models\Faq;
use App\Models\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
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
    /** Pages with their own public template. The slug is also the template key. */
    public const TEMPLATES = [
        'home' => 'Homepage',
        'about' => 'About page',
        'privacy' => 'Privacy notice',
        'terms' => 'Terms of use',
        'calculator-disclaimer' => 'Calculator disclaimer',
    ];

    /** Slugs that use the plain legal-text template. */
    public const LEGAL_SLUGS = ['privacy', 'terms', 'calculator-disclaimer'];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Page identity')
                    ->schema([
                        TextInput::make('title')->required()->maxLength(160),
                        Select::make('slug')
                            ->label('Page')
                            ->options(self::TEMPLATES)
                            ->default('home')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabledOn('edit')
                            ->live()
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('template_key', match (true) {
                                in_array($state, self::LEGAL_SLUGS, true) => 'legal',
                                default => $state ?? 'home',
                            }))
                            ->helperText('Each page can be created once. Locked after creation.'),
                        Hidden::make('template_key')->default('home'),
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
                                        Textarea::make('body')
                                            ->required()
                                            ->rows(6)
                                            ->maxLength(6000)
                                            ->helperText('Plain text. A blank line starts a new paragraph.'),
                                    ])
                                    ->minItems(1)
                                    ->maxItems(30)
                                    ->default([])
                                    ->addActionLabel('Add section')
                                    ->collapsible(),
                            ]),
                        Section::make('Page sections')
                            ->visible(fn (Get $get): bool => ! self::isLegal($get))
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
                                                Textarea::make('body')->maxLength(400),
                                            ]),
                                        Block::make('team')
                                            ->label('Leadership')
                                            ->schema([
                                                Text::make('Lists the published people from Website → Team, with their portrait, role and biography.'),
                                                TextInput::make('eyebrow')->required()->maxLength(80),
                                                TextInput::make('title')->required()->maxLength(160),
                                                Textarea::make('body')->maxLength(400),
                                            ]),
                                        Block::make('packages')
                                            ->label('Engagement packages')
                                            ->schema([
                                                Text::make('Lists the published packages from Website → Packages, with a link to compare them. Hidden while none are published.'),
                                                TextInput::make('eyebrow')->required()->maxLength(80),
                                                TextInput::make('title')->required()->maxLength(160),
                                                Textarea::make('body')->maxLength(400),
                                            ]),
                                        Block::make('intro')
                                            ->label('Introduction')
                                            ->schema([
                                                TextInput::make('eyebrow')->required()->maxLength(80),
                                                TextInput::make('title')->required()->maxLength(160),
                                                Textarea::make('body')->required()->maxLength(1000),
                                            ]),
                                        Block::make('audience')
                                            ->label('Who we help')
                                            ->schema([
                                                TextInput::make('eyebrow')->required()->maxLength(80),
                                                TextInput::make('title')->required()->maxLength(160),
                                                Textarea::make('body')->required()->maxLength(1000),
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

    /** Fields inside draft_data read the template from the page level. */
    private static function isHome(Get $get): bool
    {
        $template = $get('../template_key') ?? $get('template_key');

        return ! is_string($template) || $template === 'home';
    }
}
