<?php

namespace LucaLongo\Subscriptions\Filament\Tables;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

interface TableContract
{
    public static function make(Table $table, ?Model $ownerRecord = null): Table;
}
