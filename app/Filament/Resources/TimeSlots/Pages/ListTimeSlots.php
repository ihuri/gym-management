<?php

namespace App\Filament\Resources\TimeSlots\Pages;

use App\Filament\Resources\TimeSlots\TimeSlotResource;
use App\Models\TimeSlot;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTimeSlots extends ListRecords
{
    protected static string $resource = TimeSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Novo Horário'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos os Dias')
                ->badge(TimeSlot::query()->where('active', true)->count()),
            'segunda' => Tab::make('Segunda')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', 1))
                ->badge(TimeSlot::query()->where('day_of_week', 1)->where('active', true)->count()),
            'terca' => Tab::make('Terça')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', 2))
                ->badge(TimeSlot::query()->where('day_of_week', 2)->where('active', true)->count()),
            'quarta' => Tab::make('Quarta')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', 3))
                ->badge(TimeSlot::query()->where('day_of_week', 3)->where('active', true)->count()),
            'quinta' => Tab::make('Quinta')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', 4))
                ->badge(TimeSlot::query()->where('day_of_week', 4)->where('active', true)->count()),
            'sexta' => Tab::make('Sexta')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', 5))
                ->badge(TimeSlot::query()->where('day_of_week', 5)->where('active', true)->count()),
            'sabado' => Tab::make('Sábado')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', 6))
                ->badge(TimeSlot::query()->where('day_of_week', 6)->where('active', true)->count()),
            'domingo' => Tab::make('Domingo')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', 0))
                ->badge(TimeSlot::query()->where('day_of_week', 0)->where('active', true)->count()),
        ];
    }
}
