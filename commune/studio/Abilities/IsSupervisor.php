<?php


namespace Commune\Studio\Abilities;


use Commune\Chatbot\App\Abilities\Supervise;
use Commune\Chatbot\Blueprint\Conversation\Conversation;

class IsSupervisor implements Supervise
{
    protected $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function isAllowing(Conversation $conversation): bool
    {
        $id = $conversation->getUser()->getId();
        return in_array($id, $this->ids);
    }

}