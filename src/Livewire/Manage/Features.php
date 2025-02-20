<?php

namespace LucaLongo\Subscriptions\Livewire\Manage;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use LucaLongo\Subscriptions\Filament\Forms\PlanFeatureForm;
use LucaLongo\Subscriptions\Filament\Tables\PlanFeatureTable;

class Features extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function render(): View
    {
        return view('subscriptions::livewire.manage.features');
    }

    public function table(Table $table): Table
    {
        return PlanFeatureTable::make($table);
    }

    public function form(Form $form): Form
    {
        return PlanFeatureForm::make($form);
    }
}
