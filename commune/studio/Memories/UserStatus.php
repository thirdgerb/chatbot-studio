<?php


namespace Commune\Studio\Memories;


use Commune\Chatbot\App\Memories\MemoryDef;
use Commune\Chatbot\OOHost\Session\Scope;

/**
 * @property int $loginTimes
 */
class UserStatus extends MemoryDef
{
    const DESCRIPTION = '用户的部分信息';

    const SCOPE_TYPES = [Scope::USER_ID];

    protected function init(): array
    {
        return [
            'loginTimes' => 1,
        ];
    }

}