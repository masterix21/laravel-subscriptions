<?php

namespace LucaLongo\Subscriptions\Filament\Forms;

use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

interface FormContract
{
    public static function make(Schema $form, ?Model $ownerRecord = null): Schema;
}
