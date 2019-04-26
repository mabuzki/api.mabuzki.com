<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
//leez
use Illuminate\Foundation\AliasLoader;
//leez

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        // $this->app->singleton('admin', function(){
        //     return new AdminService ();
        // });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        //leez
        \Carbon\Carbon::setLocale('zh');

        if(request()->is('admin*') || app()->runningInConsole()) {
            AliasLoader::getInstance()->alias('admin',Admin::class);
            // $this->app-register(AdminServiceProvider::class);
        }
        //leez
    }
}
