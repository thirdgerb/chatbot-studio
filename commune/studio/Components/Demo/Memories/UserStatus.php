<?php


namespace Commune\Studio\Components\Demo\Memories;


use Commune\Chatbot\App\Memories\MemoryDef;
use Commune\Chatbot\OOHost\Session\Scope;

/**
 * @property int $loginTimes
 * @property bool $toldWhatToDo
 * @property bool $testConversation
 * @property bool $testManager
 * @property bool $testShell
 *
 */
class UserStatus extends MemoryDef
{
    const DESCRIPTION = '用户的部分信息';

    const SCOPE_TYPES = [Scope::USER_ID];

    protected function init(): array
    {
        return [
            'loginTimes' => 0,
            'toldWhatToDo' => false,
            'testConversation' => false,
            'testManager' => false,
        ];
    }

}