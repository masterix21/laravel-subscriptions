<?php

namespace LucaLongo\Subscriptions\Filament\Tables;

use Filament\Forms\Form;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
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
                    ->form(fn (Form $form) => FeatureForm::make($form)),

                DeleteAction::make()->iconButton(),
            ]);
    }
}
