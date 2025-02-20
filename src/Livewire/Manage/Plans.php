<?php

namespace LucaLongo\Subscriptions\Livewire\Manage;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use LucaLongo\Subscriptions\Filament\Forms\PlanResourceForm;
use LucaLongo\Subscriptions\Filament\Tables\PlanResourceTable;

class Plans extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?Model $subscribable = null;

    public function render(): View
    {
        return view('subscriptions::livewire.manage.plans');
    }

    public function form(Form $form): Form
    {
        return PlanResourceForm::make($form);
    }

    public function table(Table $table): Table
    {
        return PlanResourceTable::make($table)
            ->query(fn ($query) => $query
                ->when($this->subscribable, fn ($query) => $query
                    ->where('subscribable_type', $this->subscribable::class)
                    ->where('subscribable_id', $this->subscribable->getKey())
                )
            );
    }
}
