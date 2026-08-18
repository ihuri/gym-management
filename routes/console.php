<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Rotina diária para verificar e gerar mensalidades, atualizar atrasos e notificar administradores
Schedule::command('app:check-payments-and-notify')->dailyAt('06:00');
