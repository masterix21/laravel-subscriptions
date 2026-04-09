<?php

namespace LucaLongo\Subscriptions\Filament\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use LucaLongo\Subscriptions\Filament\Forms\FeatureForm;

class FeatureTable implements TableContract
{
    public static function make(Table $table, ?Model $ownerRecord = null): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->description(fn ($record) => $record->code),
            ])
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->form(fn (Schema $form) => FeatureForm::make($form)),

                DeleteAction::make()->iconButton(),
            ]);
    }
}
