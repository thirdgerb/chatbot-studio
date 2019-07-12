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

    public function boot($app)
    {
    }

    public function register()
    {
        $this->app->bind(Supervise::class, function(){

            $supervisorStr = env('COMMUNE_SUPERVISORS', '');
            $supervisors = explode('|', $supervisorStr);
            $supervisors = count($supervisors) > 1 ? $supervisors : [];

            return new IsSupervisor($supervisors);
        });
    }


}