<?php

/**
 * Class AbillityServiceProvider
 * @package Commune\Studio\Providers
 * @author BrightRed
 */

namespace Commune\Studio\Providers;


use Commune\Chatbot\App\Abilities\Supervise;
use Commune\Chatbot\Blueprint\Conversation\Conversation;
use Commune\Chatbot\Blueprint\ServiceProvider;

class AbilitiesServiceProvider extends ServiceProvider
{
    protected $supervisors = [
        'testUserId',
    ];

    public function boot($app)
    {
    }

    public function register()
    {
        $this->app->bind(Supervise::class, function(){

            $ids = $this->supervisors;
            return new class($ids) implements Supervise {

                protected $ids;

                public function __construct(array $ids)
                {
                    $this->ids = $ids;
                }

                public function isAllowing(Conversation $conversation): bool
                {
                    $id = $conversation->getChat()->getUserId();
                    return in_array($id, $this->ids);
                }
            };
        });
    }


}