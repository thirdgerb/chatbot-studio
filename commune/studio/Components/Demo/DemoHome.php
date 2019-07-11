<?php


namespace Commune\Studio\Components\Demo;


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
     * 判断用户是否首次访问, 会用不一样的逻辑来接待.
     *
     * 测试 MemoryDef, goStagePipes
     *
     * @param Stage $stage
     * @return Navigator
     */
    public function __onStart(Stage $stage): Navigator
    {
        return $stage->buildTalk()
            ->action(function(Dialog $dialog){

                $status = UserStatus::from($this);
                $status->loginTimes += 1;
                $this->loginTimes = $status->loginTimes;

                if ($this->loginTimes > 1 ) {

                    return $dialog->goStage('welcome');
                } else {
                    return $dialog->goStagePipes(['firstVisit', 'welcome']);
                }

            });
    }

    /**
     * 第一次访问要有个热情的拥抱.
     *
     * 测试 interceptor, next()
     *
     * @param Stage $stage
     * @return Navigator
     */
    public function __onFirstVisit(Stage $stage) : Navigator
    {
        return $stage->buildTalk()
            ->interceptor(new FirstMeet())
            ->next();
    }


    /**
     * 正常的欢迎.
     *
     * 测试 talk, hearingComponent, SimpleChatAction, translator
     *
     * @param Stage $stage
     * @return Navigator
     */
    public function __onWelcome(Stage $stage) : Navigator
    {
        $next = $this->loginTimes == 1 ? 'confirmGuide' : 'menu';
        return $stage
            ->talk(function(Dialog $dialog, User $user){

                $dialog->say([
                        'name' => $user->getName(),
                        'times' => $this->loginTimes
                    ])
                    ->info('demo.startConversation');

                return $dialog->wait();

            }, function(Dialog $dialog, Message $message) use ($next){

                return $dialog->hear($message)
                    ->component(new ContinueOrChat('demo', $next))
                    ->end();
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
                '您能做些什么?' => 'help',
            ];
        } else {
            $menu = [];
        }

        $menu = array_merge($menu, [
            'sfi.demo.introduce',
            Cases\CaseListInt::class,
            Guide\GuideScript::class,
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
                        ->isIntentIn([
                            "Commune.Studio.Contexts.Demo",
                        ])
                        ->isAnyIntent()
                        ->fallback(new SimpleChatAction('demo'));
                })
            );

    }

    /**
     * 确认要不要使用新人指引.
     *
     * 测试 action, isPositive
     *
     * @param Stage $stage
     * @return Navigator
     */
    public function __onConfirmGuide(Stage $stage) : Navigator
    {
        return $stage->buildTalk()
            ->info('demo.introduce')
            ->askConfirm('请问您要查看"demo 使用指引"吗?')
            ->wait()
                ->hearing()
                    ->isPositive(new ToNext('guide'))
                    ->end(new ToNext('menu'));

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