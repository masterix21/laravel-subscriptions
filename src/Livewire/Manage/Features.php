<?php

namespace LucaLongo\Subscriptions\Livewire\Manage;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Features extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function render(): View
    {
        return view('subscriptions::livewire.manage.features');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(resolve(config('subscriptions.models.feature'))->query())
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->description(fn ($record) => $record->code),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->form($this->getFormSchema()),

                DeleteAction::make()->label(''),
            ])
            ->headerActions([
                CreateAction::make('create')
                    ->label(__('Create'))
                    ->model(config('subscriptions.models.feature'))
                    ->form($this->getFormSchema())
                    ->modalSubmitActionLabel(__('Create')),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label(__('Name'))
                ->required(),
        ];
    }
}
