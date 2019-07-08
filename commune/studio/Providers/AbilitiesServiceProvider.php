<?php

/**
 * Class AbillityServiceProvider
 * @package Commune\Studio\Providers
 * @author BrightRed
 */

namespace Commune\Studio\Providers;


use Commune\Chatbot\App\Abilities\Supervise;
use Commune\Chatbot\Blueprint\ServiceProvider;
use Commune\Studio\Abilities\IsSupervisor;

class AbilitiesServiceProvider extends ServiceProvider
{
    protected $supervisors = [
        'testUserId',
        '127.0.0.1',
    ];

    public function boot($app)
    {
    }

    public function register()
    {
        $this->app->bind(Supervise::class, function(){
            return new IsSupervisor($this->supervisors);
        });
    }


}