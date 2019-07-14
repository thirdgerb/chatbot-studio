<?php


namespace Commune\Studio\Components\Demo;


use Commune\Chatbot\App\Callables\Actions\Redirector;
use Commune\Chatbot\App\Callables\Actions\ToNext;
use Commune\Chatbot\App\Callables\StageComponents\Menu;
use Commune\Chatbot\App\Components\Predefined\Navigation\QuitInt;
use Commune\Chatbot\App\Components\SimpleChat\Callables\ContinueOrChat;
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
use Commune\Studio\Callables\Interceptors\FirstMeet;
use Commune\Studio\Components\Demo\Introduce\IntroduceInt;
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
        return $stage
            ->onFallback([Redirector::class, 'repeat'])
            ->talk(function(Dialog $dialog){

                $dialog->say([
                        'times' => $this->loginTimes
                    ])
                    ->info('demo.startConversation');

                return $dialog->goStage('menu');

            }, function(Dialog $dialog, Message $message){

                return $dialog->hear($message)
                    ->isAnyIntent()
                    ->interceptor(new SimpleChatAction())
                    ->end(function(Dialog $dialog){
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
                '你能做些什么?' => 'help',
            ];
        } else {
            $menu = [];
        }

        $menu = array_merge($menu, [
            'sfi.demo.introduce',
            Guide\GuideScript::class,
            Cases\NLUScript::class,
            Cases\ConversationScript::class,
            Cases\ShellScript::class,
            Cases\ManagerScript::class,
            '结束会话' => QuitInt::class,
        ]);

        return $stage->buildTalk()
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

    /**
     * 回调stage
     *
     * @param Stage $stage
     * @return Navigator
     */
    public function __onIntroduce(Stage $stage) : Navigator
    {
        return $stage->sleepTo(
            IntroduceInt::class,
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