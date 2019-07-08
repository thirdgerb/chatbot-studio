<?php


namespace Commune\Studio\SessionPipes;


use Commune\Chatbot\App\SessionPipe\EventMsgPipe;
use Commune\Chatbot\Blueprint\Message\Event\EventMsg;
use Commune\Chatbot\Framework\Messages\Events\ConnectionEvt;
use Commune\Chatbot\OOHost\Directing\Navigator;
use Commune\Chatbot\OOHost\Session\Session;

class EventMsgHandler extends EventMsgPipe
{

    protected function handleEvent(EventMsg $message, Session $session) : ? Navigator
    {
        // 举例
        if ($message->getEventName() === ConnectionEvt::class) {
            return $session->dialog->repeat();
        }

        return null;
    }


}