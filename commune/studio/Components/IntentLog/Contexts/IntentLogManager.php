<?php


namespace Commune\Studio\Components\IntentLog\Contexts;


use Commune\Chatbot\App\Callables\Intercepers\MustBeSupervisor;
use Commune\Chatbot\App\Messages\Text;
use Commune\Chatbot\Blueprint\Message\Message;
use Commune\Chatbot\Blueprint\Message\VerboseMsg;
use Commune\Chatbot\OOHost\Context\Definition;
use Commune\Chatbot\OOHost\Context\Depending;
use Commune\Chatbot\OOHost\Context\Exiting;
use Commune\Chatbot\OOHost\Context\OOContext;
use Commune\Chatbot\OOHost\Context\Stage;
use Commune\Chatbot\OOHost\Dialogue\Dialog;
use Commune\Chatbot\OOHost\Directing\Navigator;
use Commune\Studio\Components\IntentLog\IntentLogCommandsPipe;

class IntentLogManager extends OOContext
{
    const DESCRIPTION = '管理 message intent的记录';

    const CONTEXT_TAGS = [Definition::TAG_MANAGER];

    public static function __depend(Depending $depending): void
    {
    }

    public function __staging(Stage $stage) : void
    {
        $stage->onStart(new MustBeSupervisor());
    }

    public function __onStart(Stage $stage): Navigator
    {
        return $stage->buildTalk()
            ->info('管理 message intent 记录')
            ->goStage('menu');
    }

    public function __onMenu(Stage $stage) : Navigator
    {
        return $stage->buildTalk()
            ->info(
                '输入命令查看最新消息, 或发送消息给指定用户.
- 输入 ".help" 查看可用命令.
- 发送消息给指定用户, 输入格式为  "userId : 要发送的消息"

请输入:'
            )
            ->wait()
            ->hearing()
            ->middleware(IntentLogCommandsPipe::class)
            ->isInstanceOf(
                VerboseMsg::class,
                function(Dialog $dialog, Message $message) {

                    $text = $message->getTrimmedText();

                    $secs = explode(':', $text, 2);

                    if (count($secs) !== 2) {
                        return null;
                    }

                    list($userId, $msg) = $secs;

                    $name = $dialog->session
                        ->conversation
                        ->getUser()
                        ->getName();

                    $dialog->session
                        ->conversation
                        ->deliver(
                            trim($userId),
                            new Text(
                                "\n[收到 $name 的消息] : " . trim($msg) ."\n"
                            )
                        );

                    $dialog->say()
                        ->info("消息已发送");

                    return $dialog->wait();
                }
            )
            ->end();
    }

    public function __exiting(Exiting $listener): void
    {
    }


}