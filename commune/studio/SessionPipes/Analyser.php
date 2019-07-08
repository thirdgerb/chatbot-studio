<?php


namespace Commune\Studio\SessionPipes;


use Commune\Chatbot\App\Abilities\Supervise;
use Commune\Chatbot\Laravel\SessionCommands\RunningSpyCmd;
use Commune\Chatbot\App\Commands\Analysis;
use Commune\Chatbot\OOHost\Command\HelpCmd;
use Commune\Chatbot\OOHost\Command\SessionCommandPipe;
use Commune\Chatbot\OOHost\Session\Session;

/**
 * 系统管理员使用的命令.
 */
class Analyser extends SessionCommandPipe
{
    // 命令的名称.
    protected $commands = [
        HelpCmd::class,
        Analysis\WhereCmd::class,
        Analysis\MemoryCmd::class,
        Analysis\RedirectCmd::class,
        Analysis\ContextRepoCmd::class,
        RunningSpyCmd::class,
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