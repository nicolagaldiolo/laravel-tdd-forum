<?php

namespace App\Providers;

use App\Channel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

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
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        // Visibile a tutte le View (viene effettuata solo 1 query)
        // Incompatibile con la configurazione attuale dei test in quanto prima di ogni test viene fatta la migrazione del db
        // e questa query viene lanciata una volta sola prima di lanciare ogni test e quindi le migrazioni del db.
        // In sostanza, effettuo la query prima di avere le tabelle
        //\View::share('channels', Channel::all());

        // Visibile a + view (viene effettuata una query ogni volta che viene fatto il rendere della view)
        /*\View::composer('*', function ($view){ // Visibile a tutte le le view
        /*\View::composer(['threads.create', 'pippo.view'], function ($view){ // Visibile solo ad alcune view
        /*\View::composer('threads.create', function ($view){ // Visibile solo ad una view
            $view->with('channels', Channel::all());
        });*/


        \View::composer('*', function($view){
            $view->with('channels', Cache::rememberForever('channels', function (){
                return Channel::all();
            }));
            // in questo modo non ho problemi con i test in quanto la query viene lanciata ogni volta che
            // viene renderizzata una view
        });

    }
}
