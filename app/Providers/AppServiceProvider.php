<?php

namespace App\Providers;

use App\Models\Unit;
use App\Observers\UnitObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Unit::observe(UnitObserver::class);
    }
}
