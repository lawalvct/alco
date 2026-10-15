<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Support\ArticleRichText;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Service identity')
                    ->schema([
                        TextInput::make('title')->required()->maxLength(160),
                        TextInput::make('slug')
                            ->required()
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->unique(ignoreRecord: true)
                            ->disabledOn('edit')
                            ->helperText('Slugs are locked after creation to protect public links.'),
                        TextInput::make('sort_order')->numeric()->integer()->minValue(0)->maxValue(999)->default(0),
                    ])
                    ->columns(2),
                Group::make()
                    ->statePath('draft_data')
                    ->schema([
                        Section::make('Draft service content')
                            ->description('Save changes first. The visitor site changes only when you publish the saved draft.')
                            ->schema([
                                Textarea::make('summary')->required()->maxLength(350),
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
                                    ->minHeight('14rem')
                                    ->maxLength(6000)
                                    ->helperText('Pasted content is cleaned before it is displayed on the public website.'),
                                Textarea::make('audience')->maxLength(500),
                                Repeater::make('deliverables')
                                    ->label('What this service can cover')
                                    ->schema([
                                        TextInput::make('label')->required()->maxLength(160),
                                    ])
                                    ->maxItems(12)
                                    ->default([]),
                                Textarea::make('seo_description')->maxLength(160),
                            ]),
                    ]),
            ]);
    }
}
