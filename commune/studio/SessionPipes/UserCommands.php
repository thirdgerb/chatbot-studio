<?php


namespace Commune\Studio\SessionPipes;


use Commune\Chatbot\App\Commands\Navigation\BackCmd;
use Commune\Chatbot\App\Commands\Navigation\CancelCmd;
use Commune\Chatbot\App\Commands\Navigation\QuitCmd;
use Commune\Chatbot\App\Commands\UserCommandsPipe;
use Commune\Chatbot\OOHost\Command\Help;

/**
 * 用户可用的命令.
 */
class UserCommands extends UserCommandsPipe
{
    // 命令的名称.
    protected $commands = [
        Help::class,
        BackCmd::class,
        QuitCmd::class,
        CancelCmd::class,
    ];

    // 定义一个 command mark
    protected $commandMark = '#';

}