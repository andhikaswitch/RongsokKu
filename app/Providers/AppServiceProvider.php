<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // MariaDB 10.4 di XAMPP membatasi panjang kunci indeks pada utf8mb4.
        Schema::defaultStringLength(191);
    }
}
