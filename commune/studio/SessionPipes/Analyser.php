<?php


namespace Commune\Studio\SessionPipes;


use Commune\Chatbot\App\Abilities\Supervise;
use Commune\Chatbot\App\Commands\Analysis\ContextRepoCmd;
use Commune\Chatbot\App\Commands\Analysis\MemoryCmd;
use Commune\Chatbot\App\Commands\Analysis\RedirectCmd;
use Commune\Chatbot\App\Commands\Analysis\StatusCmd;
use Commune\Chatbot\App\Commands\Analysis\WhereCmd;
use Commune\Chatbot\OOHost\Command\Help;
use Commune\Chatbot\OOHost\Command\SessionCommandPipe;
use Commune\Chatbot\OOHost\Session\Session;

/**
 * 系统管理员使用的命令.
 */
class Analyser extends SessionCommandPipe
{
    // 命令的名称.
    protected $commands = [
        Help::class,
        WhereCmd::class,
        StatusCmd::class,
        MemoryCmd::class,
        RedirectCmd::class,
        ContextRepoCmd::class
    ];

    // 定义一个 command mark
    protected $commandMark = '/';

    public function handle(Session $session, \Closure $next): Session
    {
        $isSupervisor = $session->conversation
            ->isAbleTo(Supervise::class);

        if (!$isSupervisor) {
            return $next($session);
        }

        return parent::handle($session, $next);
    }


}