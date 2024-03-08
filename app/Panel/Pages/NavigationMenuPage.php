<?php

namespace App\Panel\Pages;

use App\Actions\NavigationMenu\CreateNavigationMenuAction;
use App\Actions\NavigationMenu\CreateNavigationMenuItemAction;
use App\Actions\NavigationMenu\UpdateNavigationMenuAction;
use App\Actions\NavigationMenu\UpdateNavigationMenuItemAction;
use App\Models\Enums\NavigationMenuItemType;
use App\Models\NavigationMenu;
use App\Models\NavigationMenuItem;
use Closure;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;

class NavigationMenuPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static string $view = 'panel.pages.navigation-menu';

    protected static ?string $title = 'Navigation';

    protected static ?int $navigationSort = 99;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = 'Settings';

    public function mount()
    {
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'navigationMenus' => NavigationMenu::query()
                ->with([
                    'items' => function ($query){
                        $query
                            ->ordered()
                            ->whereNull('parent_id')
                            ->with('children', function ($query){
                                $query->ordered();
                            });
                    }
                ])
                ->orderBy('id')
                ->get(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create-navigation-menu')
                ->label('Add Navigation Menu')
                ->modalWidth('xl')
                ->form($this->getNavigationMenuForm())
                ->action(function ($data) {
                    CreateNavigationMenuAction::run($data);
                }),

        ];
    }

    public function editNavigationMenuAction(): Action
    {
        return Action::make('editNavigationMenuAction')
            ->label('Edit')
            ->modalWidth('xl')
            ->icon('heroicon-s-pencil')
            ->size('xs')
            ->form($this->getNavigationMenuForm())
            ->fillForm(function (array $arguments) {
                return NavigationMenu::query()
                    ->select(['name', 'handle'])
                    ->where('id', $arguments['id'])
                    ->first()
                    ->toArray();
            })
            ->action(function (array $data, array $arguments) {
                UpdateNavigationMenuAction::run(NavigationMenu::find($arguments['id']), $data);
            });
    }

    public function deleteNavigationMenuAction(): Action
    {
        return Action::make('deleteNavigationMenuAction')
            ->label('Delete')
            ->icon('heroicon-s-trash')
            ->color('danger')
            ->size('xs')
            // ->extraAttributes([
            //     'class' => 'hidden'
            // ])
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                NavigationMenu::destroy($arguments['id']);
            });
    }

    public function addNavigationMenuItemAction(): Action
    {
        return Action::make('addNavigationMenuItemAction')
            ->label('Add Item')
            ->modalHeading('Add Navigation Menu Item')
            ->icon('heroicon-s-plus')
            ->size('xs')
            ->color('gray')
            ->modalWidth('xl')
            ->form($this->getNavigationMenuItemForm())
            ->action(function (array $data,array $arguments) {
                $data['navigation_menu_id'] = $arguments['navigation_menu_id'];

                CreateNavigationMenuItemAction::run($data);
            });
    }
    
    public function addNavigationMenuItemChildAction(): Action
    {
        return Action::make('addNavigationMenuItemChildAction')
            ->label('Add Item')
            ->modalHeading('Add Navigation Menu Item Child')
            ->icon('heroicon-s-plus')
            ->size('xs')
            ->color('gray')
            ->extraAttributes([
                'class' => 'hidden'
            ])
            ->modalWidth('xl')
            ->form($this->getNavigationMenuItemForm())
            ->action(function (array $data,array $arguments) {
                $data['parent_id'] = $arguments['parent_id'];
                $data['navigation_menu_id'] = $arguments['navigation_menu_id'];

                CreateNavigationMenuItemAction::run($data);
            });
    }



    public function editNavigationMenuItemAction(): Action
    {
        return Action::make('editNavigationMenuItemAction')
            ->label('Edit Item')
            ->modalHeading('Edit Navigation Menu Item')
            ->extraAttributes([
                'class' => 'hidden'
            ])
            ->fillForm(function (array $arguments) {
                return NavigationMenuItem::query()
                    ->select(['label', 'type'])
                    ->where('id', $arguments['id'])
                    ->first()
                    ->toArray();
            })
            ->color('gray')
            ->modalWidth('xl')
            ->form($this->getNavigationMenuItemForm())
            ->action(function (array $data,array $arguments) {
                UpdateNavigationMenuItemAction::run(NavigationMenuItem::find($arguments['id']), $data);
            });
    }

    public function deleteNavigationItemMenuAction(): Action
    {
        return Action::make('deleteNavigationItemMenuAction')
            ->label('Delete')
            ->icon('heroicon-s-trash')
            ->color('danger')
            ->size('xs')
            ->extraAttributes([
                'class' => 'hidden'
            ])
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                NavigationMenuItem::destroy($arguments['id']);
            });
    }

    protected function getNavigationMenuForm()
    {
        return function (array $arguments) {
            $id = $arguments['id'] ?? null;
            return [
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->reactive()
                    ->debounce()
                    ->afterStateUpdated(function (?string $state, Set $set) {
                        if (!$state) {
                            return;
                        }

                        $set('handle', Str::slug($state));
                    }),
                TextInput::make('handle')
                    ->label('Handle')
                    ->rules([
                        fn (): Closure => function (string $attribute, $value, Closure $fail) use ($id) {
                            if (NavigationMenu::query()->where('handle', $value)->where('id', '!=', $id)->exists()) {
                                $fail(__('validation.unique', ['attribute' => $value]));
                            }
                        },
                    ])
                    ->required(),
            ];
        };
    }
    
    protected function getNavigationMenuItemForm()
    {
        return function(array $arguments){
            return [
                TextInput::make('label')
                    ->required(),
                Select::make('type')
                    ->options(NavigationMenuItemType::getOptions())
                    ->required(),
            ];
        };
    }

    public function sortNavigationMenuItems($items, $parentId = null)
    {
        $startOrder = 1;
        foreach ($items as $id) {
            NavigationMenuItem::query()
                ->where('id', $id)
                ->update([
                    'order_column' => $startOrder++,
                    'parent_id' => $parentId
                ]);
        }

        // NavigationMenuItem::setNewOrder(
        //     ids : $items,
        //     modifyQuery: fn(Builder $query) => dd($query->toSql())
        // );
    }
}
