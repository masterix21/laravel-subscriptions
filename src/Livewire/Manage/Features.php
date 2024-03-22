<?php

namespace LucaLongo\Subscriptions\Livewire\Manage;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
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
                    ->label(__('Name')),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->form([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required(),
                    ])
                    ->action(function (array $data, Model $record) {
                        $record->name = $data['name'];
                        $record->save();
                    }),

                DeleteAction::make()->label(''),
            ])
            ->headerActions([
                Action::make('create')
                    ->label(__('Create'))
                    ->form([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required(),
                    ])
                    ->modalSubmitActionLabel(__('Create'))
                    ->action(function (array $data): void {
                        $model = resolve(config('subscriptions.models.feature'));
                        $model->name = $data['name'];
                        $model->save();

                        Notification::make()
                            ->success()
                            ->title(__('subscriptions::subscriptions.features.created-notification-message'))
                            ->send();
                    }),
            ]);
    }
}
