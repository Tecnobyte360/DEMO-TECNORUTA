<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Masmerise\Toaster\Http\Livewire\Toaster;
use Masmerise\Toaster\Toaster as ToasterToaster;
use Illuminate\Support\Facades\Schema;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
public function boot(): void
{
    Schema::defaultStringLength(191); 

    Livewire::component('toaster', ToasterToaster::class);
}
}
