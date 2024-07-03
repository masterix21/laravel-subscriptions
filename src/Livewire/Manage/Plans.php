<?php

namespace LucaLongo\Subscriptions\Livewire\Manage;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\RawJs;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Guava\FilamentClusters\Forms\Cluster;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use LucaLongo\Subscriptions\Enums\DurationInterval;

class Plans extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?Model $subscribable = null;

    public function render(): View
    {
        return view('subscriptions::livewire.manage.plans');
    }

    protected function getTableQuery(): Builder
    {
        return app(config('subscriptions.models.plan'))
            ->query()
            ->when($this->subscribable, fn ($q) => $q
                ->where('subscribable_type', $this->subscribable::class)
                ->where('subscribable_id', $this->subscribable->getKey()));
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->translateLabel()
                ->description(fn ($record) => $record->code),

            TextColumn::make('price')
                ->translateLabel()
                ->money('EUR')
                ->description(fn ($record) => trans_choice('subscriptions::subscriptions.cycle', $record->invoice_period, [
                    'value' => $record->invoice_period,
                    'single_interval' => $record->invoice_interval->labelSingular(),
                    'many_interval' => $record->invoice_interval->label(),
                ])),

            IconColumn::make('enabled')->translateLabel()->boolean(),

            IconColumn::make('hidden')->translateLabel()->boolean(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->label('')
                ->form($this->getFormSchema()),

            DeleteAction::make()
                ->label(''),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            CreateAction::make('create')
                ->label(__('Create'))
                ->model(config('subscriptions.models.plan'))
                ->form($this->getFormSchema())
                ->modalSubmitActionLabel(__('Create')),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
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
                        ->visible(fn ($record) => $record?->exists)
                        ->schema([
                            Repeater::make('planFeatures')
                                ->label('')
                                ->relationship()
                                ->addActionLabel(__('Add'))
                                ->simple(
                                    Select::make('feature_id')
                                        ->relationship('feature', 'name'),
                                ),
                        ]),

                    Tabs\Tab::make('Meta')->translateLabel()
                        ->schema([
                            KeyValue::make('meta')->default([])->label(''),
                        ]),
                ]),
        ];
    }
}
