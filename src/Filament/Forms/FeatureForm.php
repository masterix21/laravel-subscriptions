<?php

namespace LucaLongo\Subscriptions\Filament\Forms;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class FeatureForm implements FormContract
{
    public static function make(Schema $form, ?Model $ownerRecord = null): Schema
    {
        return $form
            ->columns(1)
            ->schema([
                TextInput::make('name')
                    ->translateLabel()
                    ->required(),

                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Meta')
                            ->translateLabel()
                            ->schema([
                                KeyValue::make('meta')->default([])->label(''),
                            ]),
                    ]),
            ]);
    }
}
