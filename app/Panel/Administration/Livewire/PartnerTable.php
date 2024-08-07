<?php

namespace App\Panel\Administration\Livewire;

use App\Actions\Stakeholders\StakeholderCreateAction;
use App\Actions\Stakeholders\StakeholderUpdateAction;
use App\Models\Stakeholder;
use App\Models\StakeholderLevel;
use App\Tables\Columns\IndexColumn;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class PartnerTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    public function render()
    {
        return view('tables.table');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Stakeholder::partners())
            ->heading('Partners')
            ->reorderable('order_column')
            ->defaultSort('order_column', 'asc')
            ->columns([
                IndexColumn::make('no.'),
                SpatieMediaLibraryImageColumn::make('logo')
                    ->collection('logo'),
                TextColumn::make('name')
                    ->description(fn(Stakeholder $record) => $record->description)
                    ->searchable(),
                ToggleColumn::make('is_shown')
                    ->label('Shown'),
            ])
            ->emptyStateHeading('No Partners')
            ->emptyStateDescription('Add a partner to get started.')
            ->headerActions([
                CreateAction::make()
                    ->label('Add Partner')
                    ->modalHeading('Create Partner')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['type'] = Stakeholder::TYPE_PARTNER;
                 
                        return $data;
                    })
                    ->modalWidth(MaxWidth::ExtraLarge)
                    ->form(fn (Form $form) => $this->form($form))
                    ->using(fn (array $data) => StakeholderCreateAction::run($data))
            ])
            ->filters([
                // ...
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth(MaxWidth::ExtraLarge)
                    ->form(fn (Form $form) => $this->form($form))
                    ->using(fn (Stakeholder $record, array $data) => StakeholderUpdateAction::run($record, $data)),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                SpatieMediaLibraryFileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    ->key('logo')
                    ->collection('logo')
                    ->alignCenter()
                    ->imageResizeUpscale(false),
                TextInput::make('name')
                    ->required(),
            ]);
    }
}
