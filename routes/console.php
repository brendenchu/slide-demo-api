<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('demo:reset')
    ->daily()
    ->timezone('America/Vancouver')
    ->when(fn () => config('demo.enabled'));
