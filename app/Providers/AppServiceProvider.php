<?php

namespace App\Providers;

use App\Services\Forge\Api\ForgeClient;
use App\Services\Forge\Api\ForgeConnector;
use App\Services\Forge\Api\SaloonForgeClient;
use App\Services\Forge\Api\Support\CursorPaginator;
use App\Services\Forge\ForgeService;
use App\Services\Forge\ForgeSetting;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //
    }

    public function register(): void
    {
        $this->app->singleton(ForgeClient::class, function () {
            $setting = new ForgeSetting();

            return new SaloonForgeClient(
                connector: new ForgeConnector($setting->token, (int) $setting->timeoutSeconds),
                paginator: new CursorPaginator(),
                organization: $setting->organization,
            );
        });

        $this->app->singleton(ForgeService::class, function () {
            $setting = new ForgeSetting();

            return new ForgeService(
                $setting,
                app(ForgeClient::class)
            );
        });
    }
}
