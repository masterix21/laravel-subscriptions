<?php

namespace LucaLongo\Subscriptions\Filament\Tables;

use Filament\Schemas\Schema;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use LucaLongo\Subscriptions\Filament\Forms\SubscriptionForm;
use LucaLongo\Subscriptions\Models\Plan;
use LucaLongo\Subscriptions\Models\Subscription;

class SubscriptionTable implements TableContract
{
    public static function make(Table $table, ?Model $ownerRecord = null): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_active')
                    ->label('Active')->translateLabel()
                    ->translateLabel()
                    ->boolean(),

                TextColumn::make('plan.name')
                    ->translateLabel()
                    ->description(function (Subscription $record): string {
                        /** @var Plan $plan */
                        $plan = $record->plan;

                        return '€ '.$record->price.' '.trans_choice('subscriptions::subscriptions.cycle', $plan->duration_period, [
                            'value' => $plan->duration_period,
                            'single_interval' => $plan->duration_interval?->labelSingular(),
                            'many_interval' => $plan->duration_interval?->label(),
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
                    ->form(fn (Schema $form) => SubscriptionForm::make($form)),

                DeleteAction::make()
                    ->iconButton(),
            ]);
    }
}
