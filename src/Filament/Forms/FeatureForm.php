<?php

namespace LucaLongo\Subscriptions\Filament\Forms;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;

class FeatureForm implements FormContract
{
    public static function make(Form $form, ?Model $ownerRecord = null): Form
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
