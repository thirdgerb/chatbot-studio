<?php


namespace Commune\Studio\Components\IntentLog;


use Commune\Chatbot\OOHost\Session\Session;
use Commune\Chatbot\OOHost\Session\SessionPipe;

class IntentLogPipe implements SessionPipe
{
    /**
     * @var IntentLogRepository
     */
    protected $repository;

    /**
     * IntentLogPipe constructor.
     * @param IntentLogRepository $repository
     */
    public function __construct(IntentLogRepository $repository)
    {
        $this->repository = $repository;
    }


    public function handle(Session $session, \Closure $next): Session
    {
        $session = $next($session);

        /**
         * @var Session $session
         */
        $this->repository->logMessage($session);
        return $session;
    }


}