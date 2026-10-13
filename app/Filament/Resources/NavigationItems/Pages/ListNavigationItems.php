<?php

namespace App\Filament\Resources\NavigationItems\Pages;

use App\Filament\Resources\NavigationItems\NavigationItemResource;
use App\Models\NavigationItem;
use App\Support\Navigation;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListNavigationItems extends ListRecords
{
    protected static string $resource = NavigationItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Add link'),
        ];
    }

    /** @return array<string, Tab> */
    public function getTabs(): array
    {
        $tabs = [];

        foreach (NavigationItem::LOCATIONS as $location => $label) {
            $tabs[$location] = Tab::make("{$label} menu")
                ->modifyQueryUsing(fn (Builder $query) => $query->where('location', $location));
        }

        return $tabs;
    }

    /**
     * Reordering writes with a bulk query, which skips model events, so the
     * cached menus are cleared here as well.
     *
     * @param  array<int|string>  $order
     */
    public function reorderTable(array $order, int|string|null $draggedRecordKey = null): void
    {
        parent::reorderTable($order, $draggedRecordKey);

        Navigation::flush();
    }
}
