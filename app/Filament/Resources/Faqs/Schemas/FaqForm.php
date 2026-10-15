<?php

namespace App\Filament\Resources\Faqs\Schemas;

use App\Models\Faq;
use App\Support\ArticleRichText;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Question')
                    ->description('Answer plainly and avoid tax deadlines, rates or guarantees unless they have been reviewed.')
                    ->schema([
                        TextInput::make('question')
                            ->required()
                            ->maxLength(200),
                        RichEditor::make('answer')
                            ->toolbarButtons([
                                ['bold', 'italic', 'link'],
                                ['bulletList', 'orderedList'],
                                ['undo', 'redo'],
                            ])
                            ->linkProtocols(['https', 'mailto', 'tel'])
                            ->formatStateUsing(fn (mixed $state): mixed => is_string($state) ? ArticleRichText::asHtml($state) : $state)
                            ->required()
                            ->minHeight('8rem')
                            ->maxLength(3000),
                    ]),
                Section::make('Where it appears')
                    ->columns(2)
                    ->schema([
                        TextInput::make('group')
                            ->label('Group on the FAQ page')
                            ->required()
                            ->default('General')
                            ->maxLength(80)
                            ->datalist(fn (): array => Faq::query()->distinct()->orderBy('group')->pluck('group')->all()),
                        Select::make('services')
                            ->label('Also show on these service pages')
                            ->relationship('services', 'title')
                            ->multiple()
                            ->preload(),
                        Toggle::make('is_published')
                            ->label('Published')
                            ->helperText('Published questions appear on the FAQ page, on related service pages and wherever the homepage selects them.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
