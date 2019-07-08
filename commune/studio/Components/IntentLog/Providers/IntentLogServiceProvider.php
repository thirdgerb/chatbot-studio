<?php


namespace Commune\Studio\Components\IntentLog\Providers;


use Commune\Chatbot\Blueprint\ServiceProvider;
use Commune\Container\ContainerContract;
use Commune\Studio\Components\IntentLog\IntentLogRepository;

class IntentLogServiceProvider extends ServiceProvider
{
    /**
     * @var string|\Closure
     */
    protected $repositoryConcrete;

    /**
     * IntentLogServiceProvider constructor.
     * @param ContainerContract $app
     * @param string|\Closure $repository
     */
    public function __construct($app, $repository)
    {
        $this->repositoryConcrete = $repository;
        parent::__construct($app);
    }

    public function boot($app)
    {
    }

    public function register()
    {
        $this->app->singleton(
            IntentLogRepository::class,
            $this->repositoryConcrete
        );
    }


}