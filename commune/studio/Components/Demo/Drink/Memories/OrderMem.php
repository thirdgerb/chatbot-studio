<?php


namespace Commune\Studio\Components\Demo\Drink\Memories;

use Commune\Chatbot\App\Memories\MemoryDef;
use Commune\Chatbot\OOHost\Session\Scope;
use Commune\Studio\Components\Demo\Drink\Intents\WantDrinkInt;


/**
 * @property WantDrinkInt|null $last
 * @property int $times
 */
class OrderMem extends MemoryDef
{
    const DESCRIPTION = '购买的记忆';

    const SCOPE_TYPES = [Scope::USER_ID];

    protected function init(): array
    {
        return [
            'last' => null,
            'times' => 0
        ];
    }
}
