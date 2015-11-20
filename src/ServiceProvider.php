<?php

namespace Sofa\LaravelScopes;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        (new Periods)->apply();
    }
}
