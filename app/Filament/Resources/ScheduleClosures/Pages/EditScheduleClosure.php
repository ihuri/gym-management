<?php

namespace App\Filament\Resources\ScheduleClosures\Pages;

use App\Filament\Resources\ScheduleClosures\ScheduleClosureResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScheduleClosure extends EditRecord
{
    protected static string $resource = ScheduleClosureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
