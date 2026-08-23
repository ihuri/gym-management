<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Payment;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todas')
                ->badge(Payment::query()->count()),
            'pendente' => Tab::make('Pendentes')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pendente'))
                ->badge(Payment::query()->where('status', 'pendente')->count())
                ->badgeColor('warning'),
            'pago' => Tab::make('Pagas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pago'))
                ->badge(Payment::query()->where('status', 'pago')->count())
                ->badgeColor('success'),
            'atrasado' => Tab::make('Atrasadas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'atrasado'))
                ->badge(Payment::query()->where('status', 'atrasado')->count())
                ->badgeColor('danger'),
            'cancelado' => Tab::make('Canceladas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelado'))
                ->badge(Payment::query()->where('status', 'cancelado')->count())
                ->badgeColor('gray'),
        ];
    }
}
