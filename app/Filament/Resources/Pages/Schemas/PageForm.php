<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Resources\HomepageHeroSlides\HomepageHeroSlideResource;
use App\Models\Faq;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Page identity')
                    ->schema([
                        TextInput::make('title')->required()->maxLength(160),
                        Select::make('slug')
                            ->options(['home' => 'Homepage'])
                            ->default('home')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabledOn('edit')
                            ->helperText('The homepage is the only editable page template in this first CMS pass.'),
                        Select::make('template_key')
                            ->options(['home' => 'Homepage'])
                            ->default('home')
                            ->required(),
                    ])
                    ->columns(2),
                Group::make()
                    ->statePath('draft_data')
                    ->schema([
                        Section::make('Draft hero copy')
                            ->description('Save changes first. The visitor site changes only when you publish the saved draft. The banner images under this text are managed in Website → Homepage slider and go live as soon as a slide is shown.')
                            ->headerActions([
                                Action::make('manageSlider')
                                    ->label('Manage homepage slider')
                                    ->icon(Heroicon::OutlinedFilm)
                                    ->link()
                                    ->url(fn (): string => HomepageHeroSlideResource::getUrl('index')),
                            ])
                            ->schema([
                                TextInput::make('hero_eyebrow')->required()->maxLength(100),
                                TextInput::make('hero_line_one')->required()->maxLength(120),
                                TextInput::make('hero_line_two')->required()->maxLength(120),
                                TextInput::make('hero_accent')->required()->maxLength(120),
                                Textarea::make('hero_description')->required()->maxLength(400),
                                TextInput::make('hero_tagline')
                                    ->label('Line above the slider')
                                    ->maxLength(120)
                                    ->placeholder('Clarity today. Confidence for what comes next.')
                                    ->helperText('Short line shown just above the homepage slider. Leave empty to hide it.'),
                                Textarea::make('seo_description')->maxLength(160),
                            ])
                            ->columns(2),
                        Section::make('Homepage sections')
                            ->description('Reorder the approved content blocks below the service cards.')
                            ->schema([
                                Builder::make('blocks')
                                    ->blocks([
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
                                            ->label('Getting started')
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
}
