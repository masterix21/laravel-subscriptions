<?php

namespace LucaLongo\Subscriptions\Filament\Tables;

use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use LucaLongo\Subscriptions\Actions\CreateSubscription;
use LucaLongo\Subscriptions\Filament\Forms\SubscriptionForm;
use LucaLongo\Subscriptions\Models\Plan;
use LucaLongo\Subscriptions\Models\Subscription;

class SubscriptionTable implements TableContract
{
    public static function make(Table $table, ?Model $ownerRecord = null): Table
    {
        return $table
            ->query(
                app(config('subscriptions.models.subscription'))
                    ->query()
                    ->with(['plan'])
                    ->when(! $ownerRecord, fn ($query) => $query->with('subscriber'))
                    ->when($ownerRecord, fn ($query) => $query
                        ->where('subscriber_type', $ownerRecord::class)
                        ->where('subscriber_id', $ownerRecord->getKey())
                    )
            )
            ->columns([
                IconColumn::make('is_active')
                    ->label('Active')->translateLabel()
                    ->translateLabel()
                    ->boolean(),

                TextColumn::make('plan.name')
                    ->translateLabel()
                    ->description(function (Subscription $record): string {
                        return '€ '.$record->price.' '.trans_choice('subscriptions::subscriptions.cycle', $record->plan->invoice_period, [
                                'value' => $record->plan->invoice_period,
                                'single_interval' => $record->plan->invoice_interval?->labelSingular(),
                                'many_interval' => $record->plan->invoice_interval?->label(),
                            ]);
                    }),

                TextColumn::make('subscriber.label')
                    ->visible(fn () => ! $ownerRecord)
                    ->translateLabel(),

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

                TextColumn::make('next_billing_at')
                    ->label('Next billing at')
                    ->dateTime()
                    ->translateLabel(),
            ])
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->form(fn (Form $form) => SubscriptionForm::make($form)),

                DeleteAction::make()
                    ->iconButton(),
            ])
            ->headerActions([
                Action::make('add')
                    ->visible(filled($ownerRecord))
                    ->translateLabel()
                    ->fillForm(function () use ($ownerRecord) : array {
                        if (! $ownerRecord) {
                            return [];
                        }

                        return [
                            'subscriber_type' => $ownerRecord::class,
                            'subscriber_id' => $ownerRecord->getKey(),
                        ];
                    })
                    ->form([
                        Select::make('plan_id')
                            ->translateLabel()
                            ->relationship(name: 'plan', titleAttribute: 'name')
                            ->searchable(['name'])
                            ->preload()
                            ->required(),
                    ])
                    ->action(function ($data) use ($ownerRecord) {
                        (new CreateSubscription)->execute(
                            plan: Plan::find($data['plan_id']),
                            subscriber: $ownerRecord,
                        );

                        Notification::make()
                            ->title(__('Subscription created'))
                            ->success()
                            ->send();
                    }),

                CreateAction::make()
                    ->form(fn (Form $form) => SubscriptionForm::make($form, $ownerRecord))
                    ->visible(filled($ownerRecord))
                    ->translateLabel()
                    ->model(config('subscriptions.models.subscription'))
                    ->fillForm(function () use ($ownerRecord) : array {
                        if (! $ownerRecord) {
                            return [];
                        }

                        return [
                            'subscriber_type' => $ownerRecord::class,
                            'subscriber_id' => $ownerRecord->getKey(),
                        ];
                    })
                    ->modalSubmitActionLabel(__('Add'))
            ]);
    }
}
