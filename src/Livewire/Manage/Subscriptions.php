<?php

namespace LucaLongo\Subscriptions\Livewire\Manage;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Support\RawJs;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Guava\FilamentClusters\Forms\Cluster;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use LucaLongo\Subscriptions\Actions\CreateSubscription;
use LucaLongo\Subscriptions\Models\Plan;
use LucaLongo\Subscriptions\Models\Subscription;

class Subscriptions extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public Model $subscriber;

    public function render(): View
    {
        return view('subscriptions::livewire.manage.subscriptions');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                resolve(config('subscriptions.models.subscription'))
                    ->query()
                    ->with(['plan'])
                    ->when($this->subscriber, fn ($query) => $query
                        ->where('subscriber_type', $this->subscriber::class)
                        ->where('subscriber_id', $this->subscriber->getKey())
                    )
            )
            ->columns([
                IconColumn::make('is_active')
                    ->label('Active')->translateLabel()
                    ->translateLabel()
                    ->boolean(),

                TextColumn::make('plan.name')
                    ->translateLabel()
                    ->description(function (Subscription $record) {
                        return '€ '. $record->price .' '. trans_choice('subscriptions::subscriptions.cycle', $record->plan->invoice_period, [
                            'value' => $record->plan->invoice_period,
                            'single_interval' => $record->plan->invoice_interval->labelSingular(),
                            'many_interval' => $record->plan->invoice_interval->label(),
                        ]);
                    }),

                TextColumn::make('starts_at')
                    ->label('Validity period')
                    ->date()
                    ->translateLabel()
                    ->description(function (Subscription $record) {
                        if (! $record->ends_at) {
                            return __('-');
                        }

                        return $record->ends_at->translatedFormat(Table::$defaultDateDisplayFormat);
                    }),
            ])
            ->headerActions([
                Action::make('add')
                    ->link()
                    ->translateLabel()
                    ->fillForm([
                        'subscriber_type' => $this->subscriber::class,
                        'subscriber_id' => $this->subscriber->getKey(),
                    ])
                    ->form([
                        Select::make('plan_id')
                            ->translateLabel()
                            ->relationship(name: 'plan', titleAttribute: 'name')
                            ->searchable(['name'])
                            ->preload()
                            ->required(),
                    ])
                    ->action(function ($data) {
                        (new CreateSubscription())->execute(
                            plan: Plan::find($data['plan_id']),
                            subscriber: $this->subscriber,
                        );

                        Notification::make()
                            ->title(__('Subscription created'))
                            ->success()
                            ->send();
                    }),

                CreateAction::make('create')
                    ->translateLabel()
                    ->model(config('subscriptions.models.feature'))
                    ->fillForm([
                        'subscriber_type' => $this->subscriber::class,
                        'subscriber_id' => $this->subscriber->getKey(),
                    ])
                    ->form($this->getFormSchema())
                    ->modalSubmitActionLabel(__('Add')),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->form($this->getFormSchema()),

                DeleteAction::make()
                    ->label(''),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
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
                            Cluster::make([
                                DateTimePicker::make('starts_at')
                                    ->native(false)
                                    ->translateLabel()
                                    ->required(),

                                DateTimePicker::make('ends_at')
                                    ->native(false)
                                    ->translateLabel()
                                    ->nullable(),
                            ])->label('Validity period')->translateLabel(),

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

                            Cluster::make([
                                DateTimePicker::make('trial_starts_at')
                                    ->native(false)
                                    ->nullable()
                                    ->translateLabel(),

                                DateTimePicker::make('trial_ends_at')
                                    ->native(false)
                                    ->nullable()
                                    ->translateLabel(),
                            ])->label('Trial period')->translateLabel(),

                            Cluster::make([
                                DateTimePicker::make('grace_starts_at')
                                    ->native(false)
                                    ->nullable()
                                    ->translateLabel(),

                                DateTimePicker::make('grace_ends_at')
                                    ->native(false)
                                    ->nullable()
                                    ->translateLabel(),
                            ])->label('Grace period')->translateLabel(),

                            Grid::make()->columns()->schema([
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
                                ->default([])
                                ->label('')
                                ->translateLabel(),
                        ]),
                ]),
        ];
    }
}
