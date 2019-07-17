<?php


namespace Commune\Studio\Components\Demo\Cases;


use Commune\Chatbot\App\Callables\Actions\Redirector;
use Commune\Chatbot\App\Contexts\TaskDef;
use Commune\Chatbot\OOHost\Context\Depending;
use Commune\Chatbot\OOHost\Context\Exiting;
use Commune\Chatbot\OOHost\Context\Stage;
use Commune\Chatbot\OOHost\Dialogue\Dialog;
use Commune\Chatbot\OOHost\Directing\Navigator;
use Illuminate\Support\Collection;

class ChatTask extends TaskDef
{
    /**
     * @var string
     */
    const DESCRIPTION = '自然语言闲聊测试';

    public static function __depend(Depending $depending): void
    {
    }

    public function __onStart(Stage $stage): Navigator
    {
        $builder = $stage
            ->buildTalk();

        $memory = $this->getSession()->memory;
        $t = $memory['on_user']['nlu.chat'] ?? 0;
        $t += 1;
        $memory['on_user']['nlu.chat'] = $t;


        if ($t === 1) {
            $builder->info(
                <<<EOF
本 demo 的重点在于多轮对话, 也使用开源项目 rasa 简单做了自然语言的闲聊功能.

配置, 训练, 打磨闲聊机器人的时间成本很高, 而且闲聊并非本项目重点, 目前仅做了简单的闲聊.
未来NLU部分应该是调用AI云的api

EOF

            );

        }

        return $builder->info(
            <<<EOF
可以试着和我说说 "你好" 类似的问候语测试. 输入 'b' 退出测试.

可在此 https://github.com/thirdgerb/chatbot-studio/blob/master/commune/data/chats/demo.yml 看到所有已配置的闲聊内容.

由于语料非常少, 目前自然语言识别效果有限. 您可以尝试以下对白:

- 你好
- 讲笑话
- 你好蠢啊
- 谢谢
- 如何联系你
- 新说唱谁能夺冠
EOF
        )
            ->wait()
            ->hearing()
            ->is('b', [Redirector::class, 'fulfill'])
            ->hasKeywords([['笑话', '说笑', '逗', '搞笑']], $this->insertIntent('ask.joke'))
            ->hasKeywords([ ['联系', 'email']], $this->insertIntent('introduce.contact'))
            ->hasKeywords(['说唱', ['夺冠', '冠军', '第一', '牛']], $this->insertIntent('rapofchina.champion'))
            ->hasKeywords([['笨', '蠢', '傻', '呆']], $this->insertIntent('attitudes.diss'))
            ->defaultFallback()
            ->end(function(Dialog $dialog){

                $intent = $dialog->session->incomingMessage->getMostPossibleIntent();

                $msg = isset($intent) ? "命中意图 $intent;" : '未命中任何意图;';

                $dialog
                    ->say()
                    ->info($msg. '
没有命中任何闲聊 (由于缺乏语料和时间, 所以精度有限). 
请继续输入句子测试. 输入"b"退出测试');

                return $dialog->wait();
            });
    }


    protected function insertIntent(string $intent) : \Closure
    {
        $intent = trim($intent);
        return function(Dialog $dialog) use ($intent) {
            $incoming = $dialog->session->incomingMessage;

            $possible = $incoming->getMostPossibleIntent();
            if (isset($possible) && $possible == $intent) {
                return null;
            }

            $dialog->say()->info("(没命中意图, 命中了本地关键字...)");

            $incoming->addPossibleIntent(
                $intent,
                new Collection([]),
                100
            );

            $names = $incoming->getHighlyPossibleIntentNames();
            $names[] = $intent;
            $incoming->setHighlyPossibleIntentNames($names);

            return null;
        };
    }

    public function __exiting(Exiting $listener): void
    {
    }


}