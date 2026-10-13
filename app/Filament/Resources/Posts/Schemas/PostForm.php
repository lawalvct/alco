<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\ArticleCategory;
use App\Models\ArticleTag;
use App\Models\MediaAsset;
use App\Models\Post;
use App\Models\Service;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Article identity')
                ->columns(2)
                ->schema([
                    TextInput::make('title')->required()->maxLength(180),
                    TextInput::make('slug')
                        ->required()
                        ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                        ->unique(ignoreRecord: true)
                        ->disabledOn('edit')
                        ->helperText('Used in the public URL. It is locked after the article is created.'),
                    Select::make('review_status')
                        ->label('Editorial status')
                        ->options(Post::REVIEW_STATUSES)
                        ->required()
                        ->default('draft')
                        ->helperText('Only an approved article can be published.'),
                ]),
            Group::make()
                ->statePath('draft_data')
                ->schema([
                    Section::make('Draft article')
                        ->description('Save changes first. Visitors see this version only after publication. Write plain text paragraphs; the public site renders it safely without raw HTML.')
                        ->columns(2)
                        ->schema([
                            TextInput::make('author_name')->label('Author')->required()->maxLength(120),
                            TextInput::make('reviewer_name')
                                ->label('Accounting or tax reviewer')
                                ->maxLength(120)
                                ->helperText('Required with a review date before a Tax & Compliance article can be published.'),
                            DatePicker::make('reviewed_at')
                                ->label('Review date')
                                ->native(false)
                                ->helperText('Required for Tax & Compliance publication.'),
                            Select::make('cover_media_asset_id')
                                ->label('Cover image')
                                ->options(fn (): array => MediaAsset::query()->orderBy('title')->pluck('title', 'id')->all())
                                ->searchable()
                                ->preload()
                                ->helperText('Choose an approved image from Media. The selected cover changes when this draft is published.'),
                            Select::make('category_ids')
                                ->label('Categories')
                                ->multiple()
                                ->required()
                                ->options(fn (): array => ArticleCategory::query()->orderBy('sort_order')->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->preload()
                                ->helperText('Category changes are included in the saved draft.'),
                            Select::make('tag_ids')
                                ->label('Tags')
                                ->multiple()
                                ->options(fn (): array => ArticleTag::query()->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->preload(),
                            Textarea::make('excerpt')
                                ->required()
                                ->rows(3)
                                ->maxLength(350)
                                ->columnSpanFull(),
                            Textarea::make('body')
                                ->required()
                                ->rows(16)
                                ->maxLength(16000)
                                ->helperText('Separate paragraphs with a blank line. Use clear heading-style opening sentences because rich HTML is intentionally not accepted here.')
                                ->columnSpanFull(),
                            Textarea::make('seo_description')->maxLength(160)->columnSpanFull(),
                        ]),
                    Section::make('Sources and related content')
                        ->description('Add sources for factual or tax content. Only secure https links are shown publicly.')
                        ->columns(2)
                        ->schema([
                            Repeater::make('sources')
                                ->schema([
                                    TextInput::make('title')->label('Source title')->required()->maxLength(180),
                                    TextInput::make('url')->label('Source URL')->required()->url()->startsWith(['https://'])->maxLength(500),
                                    DatePicker::make('accessed_on')->label('Accessed on')->native(false),
                                ])
                                ->columns(3)
                                ->maxItems(12)
                                ->default([])
                                ->columnSpanFull(),
                            Select::make('related_service_ids')
                                ->label('Related services')
                                ->multiple()
                                ->options(fn (): array => Service::query()->orderBy('sort_order')->pluck('title', 'id')->all())
                                ->searchable()
                                ->preload(),
                            Select::make('related_post_ids')
                                ->label('Related articles')
                                ->multiple()
                                ->options(fn (): array => Post::query()->orderByDesc('created_at')->pluck('title', 'id')->all())
                                ->searchable()
                                ->preload(),
                        ]),
                ]),
        ]);
    }
}
