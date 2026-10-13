<?php

namespace App\Filament\Resources\ArticleCategories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArticleCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Category')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->required()->maxLength(100),
                    TextInput::make('slug')->required()->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true)->disabledOn('edit'),
                    Textarea::make('description')->rows(3)->maxLength(800)->columnSpanFull(),
                    Textarea::make('seo_description')->maxLength(160)->columnSpanFull(),
                    TextInput::make('sort_order')->label('Display order')->numeric()->integer()->minValue(0)->maxValue(999)->default(0),
                    Toggle::make('is_visible')->label('Show in public category navigation')->default(true),
                ]),
        ]);
    }
}
