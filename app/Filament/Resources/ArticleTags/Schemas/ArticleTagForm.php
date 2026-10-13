<?php

namespace App\Filament\Resources\ArticleTags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArticleTagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tag')->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(80),
                TextInput::make('slug')->required()->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true)->disabledOn('edit'),
            ]),
        ]);
    }
}
