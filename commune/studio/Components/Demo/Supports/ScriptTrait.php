<?php


namespace Commune\Studio\Components\Demo\Supports;


use Commune\Chatbot\OOHost\Context\Depending;
use Commune\Chatbot\OOHost\Context\Exiting;
use Commune\Chatbot\OOHost\Context\Hearing;
use Commune\Chatbot\OOHost\Context\Stage;

trait ScriptTrait
{

    public static function __depend(Depending $depending): void
    {
    }

    public function __exiting(Exiting $listener): void
    {
    }

    public function __staging(Stage $stage): void
    {
    }

    // 可以定义 chat
    public function __hearing(Hearing $hearing): void
    {
    }

    public function getSlots(): array
    {
        return [];
    }

}