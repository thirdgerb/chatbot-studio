<?php


namespace Commune\Studio\Components\IntentLog\Commands;


use Commune\Chatbot\Blueprint\Message\Command\CmdMessage;
use Commune\Chatbot\OOHost\Command\SessionCommand;
use Commune\Chatbot\OOHost\Command\SessionCommandPipe;
use Commune\Chatbot\OOHost\Session\Session;
use Commune\Studio\Components\IntentLog\IntentLogRepository;

class ListMessageCmd extends SessionCommand
{

    const SIGNATURE = 'list
    {--s= : (sessionId) where session_id = }
    {--c= : (chatId) where chat_id = }
    {--m= : (matchedIntent) where matched_Intent =}
    {--o= : (offset) offset is}
    {--l= : (limit)  limit is}
    {--u|unheard : if only unheard }
    ';

    const DESCRIPTION = '列出记录的输入消息';

    /**
     * @var IntentLogRepository
     */
    protected $repo;

    /**
     * ListMessage constructor.
     * @param IntentLogRepository $repo
     */
    public function __construct(IntentLogRepository $repo)
    {
        $this->repo = $repo;
    }


    public function handle(CmdMessage $message, Session $session, SessionCommandPipe $pipe): void
    {
        $limit = isset($message['--l']) ? intval($message['--l']) : 5;
        $offset = isset($message['--o']) ? intval($message['--o']) : 0;
        $chatId = $message['--c'];
        $sessionId = $message['--s'];
        $matchedIntent = $message['--m'];

        $unheard = boolval( $message['--unheard'] );

        $data = $this->repo->listMessages(
            $offset,
            $limit,
            null,
            $chatId,
            $sessionId,
            $matchedIntent,
            null,
            $unheard
        );

        $data = array_map(function($o){
            $i = (array) $o;
            return [
                'sid' => $i['session_id'],
                'cid' => $i['chat_id'],
                'uid' => $i['user_id'],
                'un' => $i['user_name'],
                'txt' => $i['message_text'],
                'mi' => $i['matched_intent'],
                'sh' => $i['session_heard'],
                'ca' => $i['created_at'],
            ];
        }, $data);

        $this->say()->info("消息如下: \n"
            . json_encode(
                $data,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_PRETTY_PRINT
            )
        );
    }


}