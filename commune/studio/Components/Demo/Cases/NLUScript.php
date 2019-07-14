<?php


namespace Commune\Studio\Components\Demo\Cases;


use Commune\Chatbot\App\Callables\Actions\Redirector;
use Commune\Chatbot\App\Callables\Actions\ToNext;
use Commune\Chatbot\App\Callables\StageComponents\Menu;
use Commune\Chatbot\App\Components\Rasa\Contexts\RasaManagerInt;
use Commune\Chatbot\App\Components\SimpleChat\Callables\SimpleChatAction;
use Commune\Chatbot\App\Components\SimpleChat\Tasks\SimpleChatTask;
use Commune\Chatbot\App\Contexts\ScriptDef;
use Commune\Chatbot\App\Traits\AskContinueTrait;
use Commune\Chatbot\OOHost\Context\Hearing;
use Commune\Chatbot\OOHost\Context\Intent\IntentMessage;
use Commune\Chatbot\OOHost\Context\Stage;
use Commune\Chatbot\OOHost\Dialogue\Dialog;
use Commune\Chatbot\OOHost\Directing\Navigator;
use Commune\Demo\App\Cases\Wheather\TellWeatherInt;
use Commune\Studio\Components\Demo\Supports\ScriptTrait;

class NLUScript extends ScriptDef
{
    use ScriptTrait, AskContinueTrait;

    const DESCRIPTION = '自然语言测试用例';

    public function __onStart(Stage $stage): Navigator
    {
        return $stage->dialog->goStage('final');
    }

    public function __onFinal(Stage $stage): Navigator
    {
        return $stage
            ->onFallback(function(Dialog $dialog) {
                return $dialog->say()->info('回到自然语言测试用例. ');
            })
            ->onFallback($this->callContinueTo('final'))
            ->component(
                (new Menu(
                    '您可能需要:',
                    [
                        '查询天气用例' => 'toWeather',
                        '闲聊测试' => 'chat',
                        RasaManagerInt::class,
                        SimpleChatTask::class,
                        '查看介绍' => 'startConversation',
                        '查看源码' => 'source',
                        '返回' => [Redirector::class, 'fulfill'],
                    ]
                ))->hearing(function(Hearing $hearing) {
                    $hearing
                        ->isAnyIntent();
                })
            );
    }


    public function __onSource(Stage $stage) : Navigator
    {
        return $stage->buildTalk()
            ->info('源码在: https://github.com/thirdgerb/chatbot-studio/blob/develop/commune/studio/Components/Demo/Cases/NLUScript.php')
            ->goStage('final');
    }

    public function __onChat(Stage $stage) : Navigator
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
本 demo 的重点在于多轮对话, 也简单做了自然语言的闲聊功能.

配置, 训练, 打磨闲聊机器人的时间成本很高, 引入语料库也价值不大, 而且闲聊并非本项目重点, 目前仅做了简单的闲聊.


EOF

            );

        }

        return $builder->info(
            <<<EOF
可以试着和我说说 "你好" 类似的问候语测试. 输入 'b' 退出测试.

可在此 https://github.com/thirdgerb/chatbot-studio/blob/master/commune/data/chats/demo.yml 看到所有已配置的闲聊内容.

您可以尝试以下对白:
- 你好
- 讲笑话
- 你是谁
- 如何联系你
- 新说唱谁能夺冠
EOF
            )
            ->wait()
            ->hearing()
            ->is('b', new ToNext('final'))
            ->interceptor(new SimpleChatAction())
            ->end(function(Dialog $dialog){

                $intent = $dialog->session->incomingMessage->getMostPossibleIntent();

                $msg = isset($intent) ? "命中意图 $intent;" : '未命中任何意图;';

                $dialog
                    ->say()
                    ->info($msg. ' 没有命中任何闲聊. 请继续输入句子测试. 输入"b"退出测试');

                return $dialog->wait();
            });

    }

    public function __onToWeather(Stage $stage) : Navigator
    {
        return $stage
            ->buildTalk()
            ->info(<<<EOF
查询天气几乎是所有对话机器人 demo 都会做的例子. 
本质上是通过 NLU 匹配到'查询天气'的意图, 同时从文本中抽取诸如地点, 日期之类的参数.
EOF
            )
            ->goStage('weather');


    }

    public function __onWeather(Stage $stage) : Navigator
    {
        return $stage
            ->onFallback($this->callContinueTo('final'))
            ->buildTalk()
            ->info(<<<EOF
您可以尝试用类似 "北京明天的天气如何" 的话语问我, 查看效果

输入 'b' 结束测试.  
EOF
)
            ->wait()
            ->hearing()
                ->is('b', new ToNext('final'))
                ->isIntent(TellWeatherInt::class)
                ->isAnyIntent(function(Dialog $dialog, IntentMessage $message) {
                    $dialog->say([
                        'name' => $message->getName(),
                        'desc' => $message->getDef()->getDesc(),
                        'entities' => json_encode(
                            $message->toAttributes(),
                            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                        )
                    ])->info(
                       "没有命中天气意图, 命中了:
意图: %name% 
简介: %desc%
参数: %entities%"
                    );

                    return $dialog->wait();
                })
                ->end(function(Dialog $dialog){

                    $dialog->say()
                        ->info('没有命中任何意图? 可以继续尝试, 输入"b"返回');
                    return $dialog->wait();
                });

    }

    public static function getScripts(): array
    {
        return [
            <<<EOF
自然语言识别技术的发展, 我认为是 对话交互方式 即将全面开花的四大技术支柱之一.

我理解的四大支柱是: 自然语言识别(语音, 语义), 智能设备 + 物联网, 富文本即时通讯工具普及, 以及多轮对话机制成熟.
EOF
            ,
            <<<EOF
自从机器学习技术推广, 自然语言技术在语音, 语义两个方面得到了长足发展. 具体应用到多轮对话的话, 语音技术目前比语义理解更成熟.

我个人认为(仅代表个人意见), 其实对于多轮对话交互而言, 纯粹自然语言理解不是绝对必要的.

极致追求对自然语言的彻底理解, 追求基于完全机器学习的多轮对话, 可能走了错路.
EOF
            ,
            <<<EOF
"多轮对话交互"  的本质在于 "交互", 而不是 "对话"; 最终目的是让人去操纵机器 (服务器, 智能硬件, 软件).

然而人是可以学习的指令的. 如同 ATM 机可以用机械按钮, 不一定需要触屏; 对话机器人也可以由机器人引导对话, 或者符合某种对话规范 (写在说明书里?) 让人去照着用.

做智能机器人不应该反而把人当成没有智力, 结果让用户和一个 "打磨期间" 的人工智障斗智斗勇, 反复猜测用什么句子才能让对方理解.

EOF
            ,
            <<<EOF
目前自然语言理解的技术还在发展中. 纯粹自然对话的机器人, 主要用于语音 骚扰(划掉) 销售电话.

就连智能客服的对话机器人, 也大量使用了猜你想问对用户意图进行引导.

我认为现阶段可能要在"交互"下更多文章, 促进对话交互技术工业化, 产业化, 才能更好地反哺"自然语言识别".
EOF
            ,
            <<<EOF
然而自然语言识别有其至关重要的好处.

最最本质的好处在于, 通过大量语料反复打磨, 让用户可以通过直觉对话, 直达意图. 既不需要去学习手册, 更不需要像浏览器进网站一样, 爬行一个层级非常深的菜单.

没有高精度自然语言识别的多轮对话技术, 在语音机器人方面的价值大一些; 否则, 用户体验并不比网页和app更好.
EOF
            ,

            <<<EOF
我目前使用的 NLU, 是基于rasa做的. rasa是一个开源项目, 我目前理解是对tensorflow, mitie 等软件做了工程上的封装, 使得我这样的小白也能使用.

rasa nlu 可以将一段话匹配意图 (目前还不适合多段话多个意图), 并提取出参数 (entity). 而 commune/chatbot 会捕获提取该结果, 抽象化成 intent 对象 (类似命令行) , 应用到后续多轮对话逻辑中.
EOF
            ,
            <<<EOF
以上就是所有介绍了. 本人是自然语言领域的外行, 谈到了很多个人理解, 贻笑大方. 若有不同意见请多指教.
EOF

        ];
    }



}