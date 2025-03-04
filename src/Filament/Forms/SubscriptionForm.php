<?php

namespace LucaLongo\Subscriptions\Filament\Forms;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Support\RawJs;
use Guava\FilamentClusters\Forms\Cluster;
use Illuminate\Database\Eloquent\Model;

class SubscriptionForm implements FormContract
{
    public static function make(Form $form, ?Model $ownerRecord = null): Form
    {
        return $form
            ->schema([
                TextInput::make('uuid')
                    ->label(__('UUID'))
                    ->visible(fn ($state) => filled($state))
                    ->readOnly(),

                Hidden::make('subscriber_type'),

                Hidden::make('subscriber_id'),

                Select::make('plan_id')
                    ->translateLabel()
                    ->relationship(name: 'plan', titleAttribute: 'name')
                    ->searchable(['name'])
                    ->preload()
                    ->required(),

                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Details')
                            ->translateLabel()
                            ->schema([
                                DateTimePicker::make('ends_at')
                                    ->native(false)
                                    ->translateLabel()
                                    ->nullable(),

                                Toggle::make('auto_renew')
                                    ->translateLabel(),

                                Grid::make()->columns()->schema([
                                    TextInput::make('price')
                                        ->translateLabel()
                                        ->mask(RawJs::make('$money($input, \'.\', \'\')'))
                                        ->stripCharacters(',')
                                        ->numeric()
                                        ->required()
                                        ->default(0),

                                    DateTimePicker::make('next_billing_at')
                                        ->native(false)
                                        ->nullable()
                                        ->translateLabel(),
                                ]),

                                DateTimePicker::make('trial_ends_at')
                                    ->native(false)
                                    ->nullable()
                                    ->translateLabel(),

                                DateTimePicker::make('grace_ends_at')
                                    ->native(false)
                                    ->nullable()
                                    ->translateLabel(),

                                Grid::make()->columns()->schema([
                                    DateTimePicker::make('canceled_at')
                                        ->native(false)
                                        ->nullable()
                                        ->translateLabel(),

                                    DateTimePicker::make('revoked_at')
                                        ->native(false)
                                        ->nullable()
                                        ->translateLabel(),
                                ]),
                            ]),

                        Tabs\Tab::make('Note')
                            ->translateLabel()
                            ->schema([
                                Textarea::make('note')
                                    ->nullable()
                                    ->translateLabel(),
                            ]),

                        Tabs\Tab::make('Meta')
                            ->translateLabel()
                            ->schema([
                                KeyValue::make('meta')
                                    ->label('')
                                    ->translateLabel(),
                            ]),
                    ]),
            ]);
    }
}
