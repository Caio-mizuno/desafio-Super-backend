<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\PixRepositoryInterface;
use App\Repositories\WithdrawalRepositoryInterface;
use App\Repositories\LogRepositoryInterface;
use App\Repositories\Eloquent\PixRepository;
use App\Repositories\Eloquent\WithdrawalRepository;
use App\Repositories\Eloquent\LogRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PixRepositoryInterface::class, PixRepository::class);
        $this->app->bind(WithdrawalRepositoryInterface::class, WithdrawalRepository::class);
        $this->app->bind(LogRepositoryInterface::class, LogRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
