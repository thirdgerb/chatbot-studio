<?php


namespace Commune\Studio\Providers;


use Commune\Chatbot\Framework\Providers\BaseServiceProvider;
use Commune\Chatbot\OOHost\Emotion\Emotions\Negative;
use Commune\Chatbot\OOHost\Emotion\Emotions\Positive;
use Commune\Chatbot\OOHost\Emotion\Feeling;

class FeelingServiceProvider extends BaseServiceProvider
{
    const IS_PROCESS_SERVICE_PROVIDER = true;


    protected $intentMap = [
        Positive::class => [

        ],
        Negative::class => [

        ]
    ];

    protected function experience(Feeling $feels) : void
    {
    }


    public function boot($app)
    {
        /**
         * @var Feeling $feels
         */
        $feels = $app->make(Feeling::class);

        foreach ($this->intentMap as $emotion => $intents) {
            $feels->setIntentMap($emotion, $intents);
        }

        $this->experience($feels);
    }

    public function register()
    {
    }


}