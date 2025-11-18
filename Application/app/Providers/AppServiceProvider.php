<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\PixRepositoryInterface;
use App\Repositories\Interfaces\WithdrawalRepositoryInterface;
use App\Repositories\Interfaces\LogRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Eloquent\PixRepository;
use App\Repositories\Eloquent\WithdrawalRepository;
use App\Repositories\Eloquent\LogRepository;
use App\Repositories\Eloquent\UserRepository;

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
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
