<?php


namespace Commune\Studio\SessionPipes;


use Commune\Chatbot\App\Commands\Analysis;
use Commune\Chatbot\App\Commands\UserCommandsPipe;
use Commune\Chatbot\App\Components\Predefined\Navigation;
use Commune\Chatbot\App\Components\Rasa\Contexts\RasaManagerInt;
use Commune\Chatbot\OOHost\Command\HelpCmd;
use Commune\Chatbot\Laravel\SessionCommands\RunningSpyCmd;

/**
 * 用户可用的命令.
 */
class UserCommands extends UserCommandsPipe
{
    // 命令的名称.
    protected $commands = [
        HelpCmd::class,
        Navigation\BackwardInt::class,
        Navigation\QuitInt::class,
        Navigation\CancelInt::class,
        Navigation\RepeatInt::class,
        Navigation\RestartInt::class,
        RasaManagerInt::class,
        Analysis\WhereCmd::class,
        Analysis\ContextRepoCmd::class,
        Analysis\WhoAmICmd::class,
        RunningSpyCmd::class,
    ];

    // 定义一个 command mark
    protected $commandMark = '#';

}