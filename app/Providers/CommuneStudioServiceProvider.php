<?php


namespace App\Providers;


use Commune\Chatbot\Framework\ChatApp;
use Commune\Container\IlluminateAdapter;
use Illuminate\Support\ServiceProvider;
use Commune\Chatbot\Blueprint\Application as ChatAppInterface;

class CommuneStudioServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            ChatAppInterface::class,
            function($app){
                $config = $app['config']['commune']['chatbot'];
                return new ChatApp($config, new IlluminateAdapter($app));
            }
        );
    }

}