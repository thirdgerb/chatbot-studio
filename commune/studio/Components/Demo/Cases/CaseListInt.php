<?php


namespace Commune\Studio\Components\Demo\Cases;


use Commune\Chatbot\App\Callables\Actions\ToNext;
use Commune\Chatbot\App\Callables\StageComponents\Menu;
use Commune\Chatbot\App\Intents\ContextIntent;
use Commune\Chatbot\OOHost\Context\Depending;
use Commune\Chatbot\OOHost\Context\Exiting;
use Commune\Chatbot\OOHost\Context\Stage;
use Commune\Chatbot\OOHost\Directing\Navigator;

class CaseListInt extends ContextIntent
{
    const DESCRIPTION = 'demo 测试用例';

    const EXAMPLES = [

    ];

    public static function __depend(Depending $depending): void
    {
    }

    public function __onStart(Stage $stage): Navigator
    {
        return $stage->component(
            (new Menu(
                '目前可以测试的用例, 按应用形式分为以下几类. 请问您想尝试哪一种? ',
                [
                    ConversationScript::class,
                    NLUScript::class,
                    ShellScript::class,
                    ManagerScript::class,
                    '返回' => new ToNext()
                ]
            ))
            ->defaultChoice(0)
        );
    }


    public function __exiting(Exiting $listener): void
    {
    }


}