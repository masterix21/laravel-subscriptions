<?php

namespace LucaLongo\Subscriptions\Filament\Forms;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Support\RawJs;
use Guava\FilamentClusters\Forms\Cluster;
use Illuminate\Database\Eloquent\Model;
use LucaLongo\Subscriptions\Enums\DurationInterval;

class PlanForm implements FormContract
{
    public static function make(Form $form, ?Model $ownerRecord = null): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Grid::make()
                    ->schema([
                        Toggle::make('enabled')->translateLabel(),

                        Toggle::make('hidden')->translateLabel(),
                    ]),

                Grid::make()
                    ->columns(3)
                    ->schema([
                        TextInput::make('code')->translateLabel()
                            ->columnSpan(2)
                            ->nullable(),

                        TextInput::make('name')->translateLabel()
                            ->columnSpan(2)
                            ->required(),

                        TextInput::make('price')->translateLabel()
                            ->mask(RawJs::make('$money($input, \'.\', \'\')'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->required()
                            ->default(0),
                    ]),

                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Details')
                            ->translateLabel()
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        DateTimePicker::make('starts_at')->translateLabel()
                                            ->native(false)
                                            ->nullable(),

                                        DateTimePicker::make('ends_at')->translateLabel()
                                            ->native(false)
                                            ->nullable(),
                                    ]),

                                Grid::make()
                                    ->columns()
                                    ->schema([
                                        Cluster::make([
                                            TextInput::make('trial_period')->translateLabel()
                                                ->numeric()->integer()->nullable(),

                                            Select::make('trial_interval')->translateLabel()
                                                ->options(DurationInterval::toOptions())
                                                ->requiredWith('trial_period'),
                                        ])->label('Trial period')->translateLabel(),

                                        Cluster::make([
                                            TextInput::make('duration_period')->translateLabel()
                                                ->numeric()->integer()->nullable(),
                                            Select::make('duration_interval')->translateLabel()
                                                ->options(DurationInterval::toOptions())
                                                ->requiredWith('duration_period'),
                                        ])->label('Duration')->translateLabel(),

                                        Cluster::make([
                                            TextInput::make('grace_period')->translateLabel()
                                                ->numeric()->integer()->nullable(),
                                            Select::make('grace_interval')->translateLabel()
                                                ->options(DurationInterval::toOptions())
                                                ->requiredWith('grace_period'),
                                        ])->label('Grace period')->translateLabel(),

                                        Cluster::make([
                                            TextInput::make('invoice_period')->translateLabel()
                                                ->numeric()->integer()->nullable(),
                                            Select::make('invoice_interval')->translateLabel()
                                                ->options(DurationInterval::toOptions())
                                                ->requiredWith('invoice_period'),
                                        ])->label('Invoice cycle')->translateLabel(),
                                    ]),
                            ]),

                        Tabs\Tab::make('Features')
                            ->translateLabel()
                            ->schema([
                                Repeater::make('planFeatures')
                                    ->hiddenLabel()
                                    ->relationship()
                                    ->addActionLabel(__('Add'))
                                    ->simple(
                                        Select::make('feature_id')
                                            ->hiddenLabel()
                                            ->searchable()
                                            ->required()
                                            ->relationship('feature', 'name')
                                    ),
                            ]),

                        Tabs\Tab::make('Meta')->translateLabel()
                            ->schema([
                                KeyValue::make('meta')->default([])
                                    ->hiddenLabel(),
                            ]),
                    ]),
            ]);
    }
}
