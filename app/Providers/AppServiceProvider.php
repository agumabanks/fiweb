<?php

namespace App\Providers;

use App\Traits\ActivationClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Traits\AddonHelper;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    use ActivationClass, AddonHelper;
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     */
    public function boot()
    {
        Paginator::useBootstrap();

        Config::set('addon_admin_routes',$this->get_addon_admin_routes());

        return 0;
    }
}
