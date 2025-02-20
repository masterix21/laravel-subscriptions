<?php

namespace LucaLongo\Subscriptions\Filament\Tables;

use Filament\Forms\Form;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use LucaLongo\Subscriptions\Filament\Forms\PlanForm;

class PlanTable implements TableContract
{
    public static function make(Table $table, ?Model $ownerRecord = null): Table
    {
        return $table
            ->query(app(config('subscriptions.models.plan'))->query())
            ->headerActions([
                CreateAction::make('create')
                    ->label(__('Create'))
                    ->model(config('subscriptions.models.plan'))
                    ->form(fn (Form $form) => PlanForm::make($form))
                    ->modalSubmitActionLabel(__('Create')),
            ])
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->form(fn (Form $form) => PlanForm::make($form)),

                DeleteAction::make()
                    ->iconButton(),
            ])
            ->columns([
                TextColumn::make('name')
                    ->translateLabel()
                    ->description(fn ($record) => $ownerRecord->code),

                TextColumn::make('price')
                    ->translateLabel()
                    ->money('EUR')
                    ->description(fn ($record) => trans_choice('subscriptions::subscriptions.cycle', $ownerRecord->invoice_period, [
                        'value' => $ownerRecord->invoice_period,
                        'single_interval' => $ownerRecord->invoice_interval?->labelSingular(),
                        'many_interval' => $ownerRecord->invoice_interval?->label(),
                    ])),

                IconColumn::make('enabled')->translateLabel()->boolean(),

                IconColumn::make('hidden')->translateLabel()->boolean(),
            ]);
    }
}
