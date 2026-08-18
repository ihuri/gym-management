<?php

namespace App\Filament\Resources\ScheduleClosures;

use App\Filament\Resources\ScheduleClosures\Pages\CreateScheduleClosure;
use App\Filament\Resources\ScheduleClosures\Pages\EditScheduleClosure;
use App\Filament\Resources\ScheduleClosures\Pages\ListScheduleClosures;
use App\Filament\Resources\ScheduleClosures\Schemas\ScheduleClosureForm;
use App\Filament\Resources\ScheduleClosures\Tables\ScheduleClosuresTable;
use App\Models\ScheduleClosure;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ScheduleClosureResource extends Resource
{
    protected static ?string $model = ScheduleClosure::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Fechamentos de Agenda';

    protected static ?string $modelLabel = 'Fechamento de Agenda';

    protected static ?string $pluralModelLabel = 'Fechamentos de Agenda';

    protected static string|UnitEnum|null $navigationGroup = 'Agenda e Horários';

    protected static ?string $recordTitleAttribute = 'reason';

    public static function form(Schema $schema): Schema
    {
        return ScheduleClosureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScheduleClosuresTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScheduleClosures::route('/'),
            'create' => CreateScheduleClosure::route('/create'),
            'edit' => EditScheduleClosure::route('/{record}/edit'),
        ];
    }
}
