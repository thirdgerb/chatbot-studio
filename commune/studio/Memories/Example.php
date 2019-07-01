<?php


namespace Commune\Studio\Memories;


use Commune\Chatbot\App\Memories\MemoryDef;
use Commune\Chatbot\OOHost\Session\Scope;

class Example extends MemoryDef
{
    const DESCRIPTION = '记忆模块的例子.';

    const SCOPE_TYPES = [Scope::SESSION_ID];

}