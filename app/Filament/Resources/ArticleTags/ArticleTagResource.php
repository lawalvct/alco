<?php

namespace App\Filament\Resources\ArticleTags;

use App\Filament\Resources\ArticleTags\Pages\CreateArticleTag;
use App\Filament\Resources\ArticleTags\Pages\EditArticleTag;
use App\Filament\Resources\ArticleTags\Pages\ListArticleTags;
use App\Filament\Resources\ArticleTags\Schemas\ArticleTagForm;
use App\Filament\Resources\ArticleTags\Tables\ArticleTagsTable;
use App\Models\ArticleTag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ArticleTagResource extends Resource
{
    protected static ?string $model = ArticleTag::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;
    protected static string|UnitEnum|null $navigationGroup = 'Website';
    protected static ?int $navigationSort = 9;
    protected static ?string $navigationLabel = 'Insight tags';
    protected static ?string $modelLabel = 'insight tag';
    protected static ?string $slug = 'insight-tags';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema { return ArticleTagForm::configure($schema); }
    public static function table(Table $table): Table { return ArticleTagsTable::configure($table); }

    public static function getPages(): array
    {
        return [
            'index' => ListArticleTags::route('/'),
            'create' => CreateArticleTag::route('/create'),
            'edit' => EditArticleTag::route('/{record}/edit'),
        ];
    }
}
