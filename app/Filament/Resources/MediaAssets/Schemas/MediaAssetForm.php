<?php

namespace App\Filament\Resources\MediaAssets\Schemas;

use App\Models\MediaAsset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class MediaAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Image')
                    ->schema([
                        self::fileUpload(),
                        self::titleInput(),
                    ]),
                Section::make('Accessibility')
                    ->schema([
                        self::altTextInput(),
                        TextInput::make('caption')
                            ->maxLength(300)
                            ->helperText('Optional visible caption, where a layout shows one.'),
                    ]),
                Section::make('Rights and attribution')
                    ->schema(self::rightsFields()),
                Section::make('File details')
                    ->visibleOn('edit')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('dimensions')
                            ->state(fn (MediaAsset $record): string => $record->dimensionsLabel()),
                        TextEntry::make('size')
                            ->state(fn (MediaAsset $record): string => $record->size ? Number::fileSize($record->size, precision: 1) : 'Unknown'),
                        TextEntry::make('usage')
                            ->label('Used on the site')
                            ->state(fn (MediaAsset $record): string => match ($count = $record->usageCount()) {
                                0 => 'Not in use',
                                1 => 'In 1 place',
                                default => "In {$count} places",
                            }),
                    ]),
            ]);
    }

    /**
     * Compact upload form for "create option" modals on other resources.
     *
     * @return list<FileUpload|TextInput|Textarea|Toggle>
     */
    public static function uploadFields(): array
    {
        return [
            self::fileUpload(),
            self::titleInput(),
            self::altTextInput(),
            ...self::rightsFields(),
        ];
    }

    private static function fileUpload(): FileUpload
    {
        return FileUpload::make('path')
            ->label('Image file')
            ->image()
            ->disk('public')
            ->directory('media')
            ->visibility('public')
            ->acceptedFileTypes(MediaAsset::ACCEPTED_MIME_TYPES)
            ->maxSize(MediaAsset::MAX_UPLOAD_KILOBYTES)
            ->storeFileNamesIn('original_name')
            ->imagePreviewHeight('220')
            ->required()
            ->helperText('JPEG, PNG or WebP up to 5 MB. Prefer WebP or JPEG for photos; SVG files are not accepted.');
    }

    private static function titleInput(): TextInput
    {
        return TextInput::make('title')
            ->label('Library name')
            ->required()
            ->maxLength(160)
            ->helperText('Helps staff find the image. Not shown to visitors.');
    }

    private static function altTextInput(): Textarea
    {
        return Textarea::make('alt_text')
            ->label('Alt text')
            ->rows(2)
            ->maxLength(300)
            ->helperText('Describe what the image shows for people using screen readers. Leave empty only for purely decorative images.');
    }

    /** @return list<TextInput|Textarea|Toggle> */
    private static function rightsFields(): array
    {
        return [
            TextInput::make('credit')
                ->label('Credit or source')
                ->maxLength(200),
            Textarea::make('usage_rights')
                ->label('Usage rights notes')
                ->rows(2)
                ->helperText('Licence, permission or where the evidence of rights is kept.'),
            Toggle::make('rights_confirmed')
                ->label('Usage rights confirmed')
                ->helperText('Required before the image can appear in the homepage slider.'),
        ];
    }
}
