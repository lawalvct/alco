<?php

namespace App\Filament\Resources\TeamMembers\Schemas;

use App\Filament\Resources\MediaAssets\Schemas\MediaAssetForm;
use App\Models\MediaAsset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeamMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile')
                    ->description('Publish only names, roles, biographies and photos that ALCO has approved.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('role')
                            ->label('Role or title')
                            ->maxLength(120)
                            ->helperText('Leave empty until the role is confirmed.'),
                        Textarea::make('bio')
                            ->label('Biography')
                            ->rows(5)
                            ->maxLength(1500)
                            ->columnSpanFull(),
                        Select::make('portrait_media_asset_id')
                            ->label('Portrait')
                            ->relationship('portrait', 'title')
                            ->getOptionLabelFromRecordUsing(fn (MediaAsset $record): string => "{$record->title} ({$record->dimensionsLabel()})")
                            ->searchable()
                            ->preload()
                            ->createOptionForm(MediaAssetForm::uploadFields())
                            ->createOptionUsing(fn (array $data): int => (int) MediaAsset::create([
                                ...$data,
                                'uploaded_by' => auth()->id(),
                            ])->getKey())
                            ->helperText('A square or portrait photo of at least 600 × 600 px works best.')
                            ->columnSpanFull(),
                        Toggle::make('is_published')
                            ->label('Show on the website')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
