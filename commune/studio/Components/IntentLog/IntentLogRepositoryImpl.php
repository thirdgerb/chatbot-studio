<?php


namespace Commune\Studio\Components\IntentLog;


use Carbon\Carbon;
use Commune\Chatbot\Blueprint\Message\VerboseMsg;
use Commune\Chatbot\Laravel\Database\TableSchema;
use Commune\Chatbot\Laravel\Drivers\LaravelDBDriver;
use Commune\Chatbot\OOHost\Session\Session;

class IntentLogRepositoryImpl implements IntentLogRepository
{

    const TABLE = 'chatbot_intent_messages';

    /**
     * @var LaravelDBDriver
     */
    protected $driver;

    /**
     * IntentLogRepositoryImpl constructor.
     * @param LaravelDBDriver $driver
     */
    public function __construct(LaravelDBDriver $driver)
    {
        $this->driver = $driver;
    }


    public function logMessage(Session $session): void
    {
        $conversation = $session->conversation;
        $incoming = $conversation->getIncomingMessage();
        $message = $incoming->message;

        if (!$message instanceof VerboseMsg) {
            return;
        }

        $data = TableSchema::getScopeFromSession($session);
        $data['user_name'] = $conversation->getUser()->getName();
        $data['message_type'] = $message->getMessageType();
        $data['message_text'] = $message->getTrimmedText();

        $matched = $session->getMatchedIntent();

        if (isset($matched)) {
            $data['matched_intent'] = $intentName = $matched->getName();
            $entities = $incoming->getPossibleIntentEntities($intentName);
            $data['matched_entities'] = json_encode(
                $entities,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
        }

        $data['nlu_intents'] = $incoming
            ->getPossibleIntentCollection()
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $data['session_heard'] = $session->isHeard() ? 1 : 0;
        $data['updated_at'] = $data['created_at'] = new Carbon();

        $this->driver
            ->getDB()
            ->table(static::TABLE)
            ->insert($data);
    }

    public function listMessages(
        int $offset = 0,
        int $limit = 20,
        array $fields = null,
        string $chatId = null,
        string $sessionId = null,
        string $matchedIntent = null,
        string $messageType = null,
        bool $sessionHeard = null
    ): array
    {
        $table = $this->driver
            ->getDB()
            ->table(static::TABLE);

        if (isset($chatId)) {
            $table->where('chat_id', $chatId);
        }

        if (isset($sessionId)) {
            $table->where('session_id',  $sessionId);
        }

        if (isset($matchedIntent)) {
            $table->where('matched_intent', $matchedIntent);
        }

        if (isset($messageType)) {
            $table->where('message_type', $messageType);
        }

        if (isset($sessionHeard)) {
            $table->where('session_heard', $sessionHeard);
        }
        $fields = $fields ?? ['*'];

        $collection = $table->offset($offset)
            ->limit($limit)
            ->orderBy('id', 'desc')
            ->get($fields);

        return $collection->toArray();
    }


}