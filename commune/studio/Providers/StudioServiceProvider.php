<?php


namespace Commune\Studio\Providers;


use Commune\Chatbot\Blueprint\ServiceProvider;
use Commune\Chatbot\Contracts\ExceptionHandler;
use Commune\Studio\Exceptions\Handler;

class StudioServiceProvider extends ServiceProvider
{
    public function boot($app): void
    {
    }

    public function register(): void
    {
        $this->app->singleton(
            ExceptionHandler::class,
            Handler::class
        );
    }


}