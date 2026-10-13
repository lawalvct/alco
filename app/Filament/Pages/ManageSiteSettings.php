<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Support\HeroImageSpec;
use App\Support\SiteSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class ManageSiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Site settings';

    protected static ?string $title = 'Site settings';

    protected static ?string $slug = 'site-settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->is_admin;
    }

    public function mount(): void
    {
        $this->form->fill(SiteSettings::all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Settings')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Business')
                            ->icon(Heroicon::OutlinedBuildingOffice2)
                            ->schema([
                                Group::make()->statePath('identity')->columns(2)->schema([
                                    TextInput::make('business_name')->required()->maxLength(120),
                                    TextInput::make('business_category')
                                        ->label('Registered business category')
                                        ->maxLength(120),
                                    TextInput::make('positioning_statement')
                                        ->maxLength(200)
                                        ->columnSpanFull(),
                                ]),
                            ]),
                        Tab::make('Contact')
                            ->icon(Heroicon::OutlinedMapPin)
                            ->schema([
                                Group::make()->statePath('contact')->columns(2)->schema([
                                    TextInput::make('street_address')->required()->maxLength(120),
                                    TextInput::make('locality')->label('Area')->required()->maxLength(80),
                                    TextInput::make('region')->label('State')->required()->maxLength(80),
                                    TextInput::make('country')->required()->maxLength(80),
                                    TextInput::make('location_summary')
                                        ->label('Short location')
                                        ->required()
                                        ->maxLength(80)
                                        ->helperText('Shown in the strip at the top of every page.'),
                                    TagsInput::make('emails')
                                        ->label('Email addresses')
                                        ->placeholder('Add an email address')
                                        ->required()
                                        ->reorderable()
                                        ->nestedRecursiveRules(['email', 'max:120'])
                                        ->helperText('Shown on the website in this order; the first is the main address. Drag to reorder.')
                                        ->columnSpanFull(),
                                    Repeater::make('phones')
                                        ->label('Phone numbers')
                                        ->columnSpanFull()
                                        ->columns(2)
                                        ->minItems(1)
                                        ->maxItems(4)
                                        ->addActionLabel('Add phone number')
                                        ->schema([
                                            TextInput::make('number')
                                                ->required()
                                                ->regex('/^\+[1-9]\d{7,14}$/')
                                                ->validationMessages(['regex' => 'Use international format with no spaces, for example +2348140824230.'])
                                                ->helperText('International format, for example +2348140824230.'),
                                            Toggle::make('whatsapp')
                                                ->label('Accepts WhatsApp messages')
                                                ->helperText('Turn on only after confirming the number uses WhatsApp.')
                                                ->inline(false),
                                        ]),
                                    TextInput::make('whatsapp_widget_number')
                                        ->label('Floating WhatsApp button number')
                                        ->regex('/^\+[1-9]\d{7,14}$/')
                                        ->validationMessages(['regex' => 'Use international format with no spaces, for example +2348140824230.'])
                                        ->placeholder('+2348140824230')
                                        ->helperText('The direct WhatsApp number used by the floating chat button. Leave blank to hide the button. This is separate from the phone list above.')
                                        ->columnSpanFull(),
                                ]),
                            ]),
                        Tab::make('Social links')
                            ->icon(Heroicon::OutlinedLink)
                            ->schema([
                                Group::make()->statePath('social')->schema([
                                    self::socialUrl('facebook_url', 'Facebook page URL'),
                                    self::socialUrl('linkedin_url', 'LinkedIn page URL'),
                                    self::socialUrl('tiktok_url', 'TikTok profile URL'),
                                ]),
                            ]),
                        Tab::make('Enquiries')
                            ->icon(Heroicon::OutlinedInbox)
                            ->schema([
                                Group::make()->statePath('forms')->schema([
                                    Toggle::make('forms_enabled')
                                        ->label('Accept enquiries through the website forms')
                                        ->helperText('Turn off to pause the contact and consultation forms, for example during a spam attack. Visitors still see the phone numbers and email address.'),
                                    TagsInput::make('notification_emails')
                                        ->label('Send new-enquiry emails to')
                                        ->placeholder('Add an email address')
                                        ->reorderable()
                                        ->nestedRecursiveRules(['email'])
                                        ->helperText('Every new enquiry is emailed to all of these addresses and saved under Enquiries, even if the email fails.'),
                                ]),
                            ]),
                        Tab::make('Homepage slider')
                            ->icon(Heroicon::OutlinedFilm)
                            ->schema([
                                Group::make()->statePath('homepage')->columns(2)->schema([
                                    Toggle::make('hero_autoplay')
                                        ->label('Move to the next slide automatically')
                                        ->helperText('Autoplay always stops for visitors who prefer reduced motion, and whenever someone pauses or uses the slider.')
                                        ->columnSpanFull(),
                                    TextInput::make('hero_interval_seconds')
                                        ->label('Time per slide')
                                        ->numeric()
                                        ->integer()
                                        ->required()
                                        ->minValue(HeroImageSpec::MIN_AUTOPLAY_SECONDS)
                                        ->maxValue(30)
                                        ->suffix('seconds'),
                                ]),
                            ]),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Save settings')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User && $user->is_admin, 403);

        $state = $this->form->getState();

        foreach (array_keys(SiteSettings::DEFAULTS) as $group) {
            if (is_array($state[$group] ?? null)) {
                SiteSettings::put($group, $state[$group], $user);
            }
        }

        Notification::make()->title('Settings saved')->body('The website now uses the updated details.')->success()->send();
    }

    private static function socialUrl(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->url()
            ->startsWith(['https://'])
            ->maxLength(255)
            ->placeholder('https://')
            ->helperText('Supplied handle: alco_solutions.ng. Add the full profile URL only after checking it; the footer shows a link only when this is filled.');
    }
}
