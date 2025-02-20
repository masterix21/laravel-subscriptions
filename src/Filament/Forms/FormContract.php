<?php

namespace LucaLongo\Subscriptions\Filament\Forms;

use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;

interface FormContract
{
    public static function make(Form $form, ?Model $ownerRecord = null): Form;
}
