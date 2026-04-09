<?php

namespace LucaLongo\Subscriptions\Filament\Tables;

use Filament\Schemas\Schema;
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
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->form(fn (Schema $form) => PlanForm::make($form)),

                DeleteAction::make()
                    ->iconButton(),
            ])
            ->columns([
                TextColumn::make('name')
                    ->translateLabel()
                    ->description(fn ($record) => $record->code),

                TextColumn::make('price')
                    ->translateLabel()
                    ->money('EUR')
                    ->description(fn ($record) => trans_choice('subscriptions::subscriptions.cycle', $record->duration_period, [
                        'value' => $record->duration_period,
                        'single_interval' => $record->duration_interval?->labelSingular(),
                        'many_interval' => $record->duration_interval?->label(),
                    ])),

                IconColumn::make('enabled')->translateLabel()->boolean(),

                IconColumn::make('hidden')->translateLabel()->boolean(),
            ]);
    }
}
