<?php

namespace App\Filament\Resources\ScheduleClosures\Pages;

use App\Filament\Resources\ScheduleClosures\ScheduleClosureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScheduleClosures extends ListRecords
{
    protected static string $resource = ScheduleClosureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
