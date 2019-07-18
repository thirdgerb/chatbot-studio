<?php


namespace Commune\Studio\Components\Demo;


use Commune\Chatbot\App\Callables\Actions\Redirector;
use Commune\Chatbot\App\Callables\Actions\ToNext;
use Commune\Chatbot\App\Callables\StageComponents\Menu;
use Commune\Chatbot\App\Components\Predefined\Navigation\QuitInt;
use Commune\Chatbot\App\Components\Rasa\Contexts\RasaManagerInt;
use Commune\Chatbot\App\Components\SimpleChat\Callables\SimpleChatAction;
use Commune\Chatbot\App\Traits\AskContinueTrait;
use Commune\Chatbot\Blueprint\Conversation\User;
use Commune\Chatbot\Blueprint\Message\Message;
use Commune\Chatbot\OOHost\Context\Depending;
use Commune\Chatbot\OOHost\Context\Exiting;
use Commune\Chatbot\OOHost\Context\Hearing;
use Commune\Chatbot\OOHost\Context\OOContext;
use Commune\Chatbot\OOHost\Context\Stage;
use Commune\Chatbot\OOHost\Dialogue\Dialog;
use Commune\Chatbot\OOHost\Directing\Navigator;
use Commune\Demo\App\Cases\Questionnaire\ReadPersonality;
use Commune\Demo\App\Cases\Wheather\TellWeatherInt;
use Commune\Studio\Components\Demo\Cases\CaseListInt;
use Commune\Studio\Components\Demo\Cases\ChatTask;
use Commune\Studio\Components\Demo\Drink\DrinkTask;
use Commune\Studio\Components\Demo\Guest\GuessNumTask;
use Commune\Studio\Components\Demo\Memories\UserStatus;

/**
 * @property int $loginTimes
 * @property bool $toldWhatToDo
 */
class DemoHome extends OOContext
{
    use AskContinueTrait;

    const DESCRIPTION = '会话起点';

    public function __construct()
    {
        parent::__construct([
            'loginTimes' => 0,
        ]);
    }


    public static function __depend(Depending $depending): void
    {
    }


    /**
     * 正常的欢迎.
     *
     * 测试 talk, hearingComponent, SimpleChatAction, translator
     *
     * @param Stage $stage
     * @return Navigator
     */
    public function __onStart(Stage $stage) : Navigator
    {
        $status = UserStatus::from($this);
        $loginTimes = $status->loginTimes;
        $loginTimes += 1;
        $status->loginTimes = $loginTimes;
        $this->loginTimes = $loginTimes;
        return $stage
            ->onFallback([Redirector::class, 'repeat'])
            ->talk(function(Dialog $dialog, User $user){

                $dialog->say([
                        'times' => $this->loginTimes
                    ])
                    ->info('demo.startConversation');

                if ($user->getName() === 'guest') {
                    $dialog->say()->warning("(由于无权限调用用户信息, 所以无法获取您名字, 见谅)");
                }


                return $dialog->goStage('menu');

            }, function(Dialog $dialog, Message $message){

                return $dialog
                    ->hear($message)
                    ->end(function(Dialog $dialog) {
                        return $dialog->repeat();
                    });
            });

    }

    /**
     * 导航菜单.
     *
     * 测试menu, isIntentIn , isAnyIntent
     *
     * @param Stage $stage
     * @return Navigator
     */
    public function __onMenu(Stage $stage) : Navigator
    {

        if (!$this->toldWhatToDo) {
            $menu = [
                '这个 demo 能做些什么?' => 'help',
            ];
        } else {
            $menu = [];
        }

        $menu = array_merge($menu, [
            '测试用例: 多轮对话购买饮料' => 'drink',
            '测试用例: 询问天气' => 'weather',
            '测试用例: 自然语言闲聊' => ChatTask::class,
            '测试用例: 15秒知道您的性格' => ReadPersonality::class,
            '测试用例: 二分法猜数字小游戏' => GuessNumTask::class,
            '测试命令: 输入 #help 查看命令' => function(Dialog $dialog){
                $dialog->say()->info("输入 #help 查看命令, 输入 #repeat 继续");
                return $dialog->wait();
            },
            '测试工具: 检查 rasa nlu 识别的意图' => RasaManagerInt::class,
            '更多测试: 测试用例列表' => CaseListInt::class,
            'commune/chatbot 项目介绍' => 'toIntro',
            '本 demo 使用指引' => Guide\GuideScript::class,
            '结束会话' => QuitInt::class,
        ]);

        return $stage
            ->buildTalk()
            ->toStage()
            ->component(
                (new Menu(
                    "您需要我做些什么? ",
                    $menu
                ))
                ->hearing(function(Hearing $hearing){
                    // 放开意图响应.
                    $hearing
                        ->isAnyIntent()
                        ->interceptor(new SimpleChatAction())
                        ->end(function(Dialog $dialog){

                            $dialog->say()
                                ->info(<<<EOF
不好意思, 没有理解您的话. 给出数字序号能选择相应功能.

项目目前还在测试阶段, 重点在多轮对话上. 自然语言识别和闲聊的部分实现有限, 尚请谅解.
EOF
                                );

                            return $dialog->repeat();
                        });
                })
            );

    }
    
    public function __onDrink(Stage $stage) : Navigator
    {
        return $stage->sleepTo(DrinkTask::class, $this->callContinueTo('menu'));
    }

    public function __onWeather(Stage $stage) : Navigator
    {
        return $stage
            ->onFallback(function(Dialog $dialog){
                $dialog->say()->info('测试结束');
                return $dialog->goStage('menu');
            })
            ->buildTalk()
            ->info(<<<EOF
本项目以 rasa 作为 NLU, 制作了简单的天气查询用例.

您可以试着询问:
- 明天天气如何?
- 北京的气温多少度
- 上海后天天气如何

看看进入多轮对话效果. 输入"b" 退出测试.
EOF
)
            ->wait()
            ->hearing()
            ->is('b', new ToNext('menu'))
            ->isIntent(TellWeatherInt::class)
            ->end(function(Dialog $dialog){
                $dialog->say()->info("没有命中天气测试. 可以输入文字继续. 输入'b'退出测试");
                return $dialog->wait();
            });
    }

    public function __onToIntro(Stage $stage) : Navigator
    {
        return $stage->buildTalk()
            ->info(
                <<<EOF
从现在开始进入 commune/chatbot 的自我介绍单元.

这即是介绍, 也是测试. 测试用 markdown 文件自动生成"猜你想问"式, 结构化的知识库.

配合自然语言识别, 可用于各种对话知识库应用. 源码在:

https://github.com/thirdgerb/chatbot-studio/tree/master/commune/data/sfi/demo

EOF

            )
            ->action($this->callContinueTo('introduce'));

    }

    /**
     * 回调stage
     *
     * @param Stage $stage
     * @return Navigator
     */
    public function __onIntroduce(Stage $stage) : Navigator
    {
        return $stage->sleepTo(
            'sfi.demo.intro',
            function(Dialog $dialog) {
                $dialog->say()->info('项目介绍结束');
                return $dialog->goStage('menu');
            }
        );

    }

    /**
     * 回调 stage
     *
     * @param Stage $stage
     * @return Navigator
     */
    public function __onGuide(Stage $stage) : Navigator
    {
        return $stage->sleepTo(
            Guide\GuideInt::class,
            function(Dialog $dialog){
                $dialog->say()->info(
                    '新手指引结束, 可以尝试输入"新手指引"再次查看(会尝试使用自然语言识别).'
                );
                return $dialog->goStage('menu');
            });
    }


    /**
     * 能干什么的提示.
     *
     * @param Stage $stage
     * @return Navigator
     */
    public function __onHelp(Stage $stage) : Navigator
    {
        $this->toldWhatToDo = true;

        return $stage->buildTalk()
            ->info('demo.introduce')
            ->askConfirm('需要查看详细的项目介绍吗?')
            ->wait()
            ->hearing()
                ->isPositive(function(Dialog $dialog){
                    return $dialog->goStage('introduce');
                })
                ->end(function(Dialog $dialog){

                    return $dialog->goStage('menu');
                });
    }

    public function __exiting(Exiting $listener): void
    {
    }


    public function __setToldWhatToDo(bool $told) : void
    {
        UserStatus::from($this)->toldWhatToDo = boolval($told);
    }


    public function __getToldWhatToDo() : bool
    {
        return (bool) UserStatus::from($this)->toldWhatToDo;
    }
}