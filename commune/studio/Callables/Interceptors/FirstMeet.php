<?php


namespace Commune\Studio\Callables\Interceptors;


use Commune\Chatbot\OOHost\Context\Callables\Interceptor;
use Commune\Chatbot\OOHost\Context\Context;
use Commune\Chatbot\OOHost\Dialogue\Dialog;
use Commune\Chatbot\OOHost\Directing\Navigator;

class FirstMeet implements Interceptor
{

    public function __invoke(
        Context $self,
        Dialog $dialog
    ): ? Navigator
    {
        $user = $dialog->session
            ->conversation
            ->getUser();

        $name = $user->getName();

        return $this->welcome($dialog, $name);
    }

    protected function welcome(Dialog $dialog, string $userName) : ? Navigator
    {
        return null;
    }


}