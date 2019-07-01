<?php


namespace Commune\Studio\Exceptions;


use Commune\Chatbot\Blueprint\Exceptions\RuntimeExceptionInterface;
use Commune\Chatbot\Blueprint\Exceptions\StopServiceExceptionInterface;
use Commune\Chatbot\Contracts\ConsoleLogger;
use Commune\Chatbot\Contracts\ExceptionHandler;

class Handler implements ExceptionHandler
{

    /**
     * @var ConsoleLogger
     */
    protected $logger;

    /**
     * Handler constructor.
     * @param ConsoleLogger $logger
     */
    public function __construct(ConsoleLogger $logger)
    {
        $this->logger = $logger;
    }


    public function reportServiceStopException(
        string $method,
        StopServiceExceptionInterface $e
    ): void
    {
        $this->logger->critical($method . ': '. $e);
    }

    public function reportRuntimeException(
        string $method,
        RuntimeExceptionInterface $e
    ): void
    {
        $this->logger->critical($method . ': '. $e);
    }


}