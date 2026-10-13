<?php

namespace App\Filament\Resources\TopicHubs\Schemas;

use App\Models\Post;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TopicHubForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Topic identity')->columns(2)->schema([
                TextInput::make('title')->required()->maxLength(180),
                TextInput::make('slug')
                    ->required()
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit')
                    ->helperText('Used in a URL such as /insights/topic/vat. Locked after creation.'),
                TextInput::make('sort_order')->label('Display order')->numeric()->integer()->minValue(0)->maxValue(999)->default(0),
            ]),
            Group::make()->statePath('draft_data')->schema([
                Section::make('Draft topic content')
                    ->description('Save changes first. The public hub reads only the last published version and only includes published articles.')
                    ->schema([
                        Textarea::make('intro')->required()->rows(4)->maxLength(1000),
                        Textarea::make('seo_description')->maxLength(160),
                        Select::make('featured_post_id')
                            ->label('Featured article')
                            ->options(fn (): array => Post::query()->orderByDesc('updated_at')->pluck('title', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->helperText('This article must be published before visitors can see it.'),
                        Select::make('post_ids')
                            ->label('Articles in this topic')
                            ->multiple()
                            ->reorderable()
                            ->options(fn (): array => Post::query()->orderByDesc('updated_at')->pluck('title', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->helperText('Drag selections into the order you want. Draft or archived articles remain hidden from visitors.'),
                    ]),
            ]),
        ]);
    }
}
