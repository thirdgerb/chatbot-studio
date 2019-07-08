<?php


namespace Commune\Studio\Components\IntentLog;


use Commune\Chatbot\App\Components\Predefined\Navigation\CancelInt;
use Commune\Chatbot\App\Components\Predefined\Navigation\RepeatInt;
use Commune\Chatbot\OOHost\Command\HelpCmd;
use Commune\Chatbot\OOHost\Command\SessionCommandPipe;
use Commune\Studio\Components\IntentLog\Commands\ListMessageCmd;

class IntentLogCommandsPipe extends SessionCommandPipe
{
    protected $commandMark = '.';


    protected $commands = [
        HelpCmd::class,
        ListMessageCmd::class,
        CancelInt::class,
        RepeatInt::class,
    ];


}