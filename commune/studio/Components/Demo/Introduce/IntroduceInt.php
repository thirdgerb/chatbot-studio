<?php


namespace Commune\Studio\Components\Demo\Introduce;


use Commune\Chatbot\App\Intents\ContextIntent;
use Commune\Chatbot\OOHost\Context\Depending;
use Commune\Chatbot\OOHost\Context\Exiting;
use Commune\Chatbot\OOHost\Context\Stage;
use Commune\Chatbot\OOHost\Directing\Navigator;

class IntroduceInt extends ContextIntent
{
    const DESCRIPTION = 'commune/chatbot 项目介绍';

    const SIGNATURE = 'demo:introduce';

    public static function __depend(Depending $depending): void
    {
    }

    public function __onStart(Stage $stageRoute): Navigator
    {
        return $stageRoute->buildTalk()
            ->info("hello world")
            ->fulfill();
    }

    public function __exiting(Exiting $listener): void
    {
        // TODO: Implement __exiting() method.
    }


}