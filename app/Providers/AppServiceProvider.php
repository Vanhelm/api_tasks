<?php

namespace App\Providers;

use App\Events\TaskCreated;
use App\Listeners\SendTaskCreatedNotification;
use App\Service\TaskService;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TaskService::class, function (Application $app) {
            return new TaskService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
