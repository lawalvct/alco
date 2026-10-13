<?php

namespace App\Filament\Resources\Faqs\Schemas;

use App\Models\Faq;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
                        Textarea::make('answer')
                            ->required()
                            ->rows(5)
                            ->maxLength(2000),
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
