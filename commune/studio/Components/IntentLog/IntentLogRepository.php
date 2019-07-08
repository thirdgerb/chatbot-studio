<?php


namespace Commune\Studio\Components\IntentLog;


use Commune\Chatbot\OOHost\Session\Session;

interface IntentLogRepository
{

    public function logMessage(Session $session) : void;

    public function listMessages(
        int $offset = 0,
        int $limit = 20,
        array $fields = null,
        string $chatId = null,
        string $sessionId = null,
        string $matchedIntent = null,
        string $messageType = null,
        bool $sessionHeard = null
    ) : array;


}