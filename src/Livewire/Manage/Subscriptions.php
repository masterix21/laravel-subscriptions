<?php

namespace LucaLongo\Subscriptions\Livewire\Manage;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use LucaLongo\Subscriptions\Filament\Forms\SubscriptionForm;
use LucaLongo\Subscriptions\Filament\Tables\SubscriptionTable;
use LucaLongo\Subscriptions\Models\Contracts\SubscriberContract;

class Subscriptions extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?SubscriberContract $subscriber = null;

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
            ->form(fn ($form) => SubscriptionForm::make($form))
            ->modalSubmitActionLabel(__('Add'));
    }
}
