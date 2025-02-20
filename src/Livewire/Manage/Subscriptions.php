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
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use LucaLongo\Subscriptions\Actions\CreateSubscription;
use LucaLongo\Subscriptions\Contracts\Subscriber;
use LucaLongo\Subscriptions\Filament\Tables\SubscriptionTable;
use LucaLongo\Subscriptions\Models\Plan;
use LucaLongo\Subscriptions\Models\Subscription;

class Subscriptions extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?Subscriber $subscriber = null;

    public function render(): View
    {
        return view('subscriptions::livewire.manage.subscriptions');
    }

    public function table(Table $table): Table
    {
        return SubscriptionTable::make($table);
    }

    protected function getCreateTableHeaderAction(): Action
    {
        return CreateAction::make('create')
            ->visible(fn () => $this->subscriber)
            ->translateLabel()
            ->model(config('subscriptions.models.subscription'))
            ->fillForm(function (): array {
                if (! $this->subscriber) {
                    return [];
                }

                return [
                    'subscriber_type' => $this->subscriber::class,
                    'subscriber_id' => $this->subscriber->getKey(),
                ];
            })
            ->form($this->getFormSchema())
            ->modalSubmitActionLabel(__('Add'));
    }
}
