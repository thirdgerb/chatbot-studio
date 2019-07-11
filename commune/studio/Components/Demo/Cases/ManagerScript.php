<?php


namespace Commune\Studio\Components\Demo\Cases;


use Commune\Chatbot\App\Callables\Actions\Redirector;
use Commune\Chatbot\App\Callables\Actions\ToNext;
use Commune\Chatbot\App\Callables\StageComponents\Menu;
use Commune\Chatbot\App\Components\NLUExamples\NLUExamplesTask;
use Commune\Chatbot\App\Components\Rasa\Contexts\RasaManagerInt;
use Commune\Chatbot\App\Components\SimpleChat\Tasks\SimpleChatTask;
use Commune\Chatbot\App\Contexts\ScriptDef;
use Commune\Chatbot\App\Traits\AskContinueTrait;
use Commune\Chatbot\OOHost\Context\Hearing;
use Commune\Chatbot\OOHost\Context\Stage;
use Commune\Chatbot\OOHost\Dialogue\Dialog;
use Commune\Chatbot\OOHost\Directing\Navigator;
use Commune\Studio\Components\Demo\Memories\UserStatus;
use Commune\Studio\Components\Demo\Supports\ScriptTrait;

class ManagerScript extends ScriptDef
{
    use ScriptTrait, AskContinueTrait;

    const DESCRIPTION = '多轮对话管理工具';


    public function __onStart(Stage $stage): Navigator
    {
        $status = UserStatus::from($this);
        $tested = $status->testManager;
        $status->testManager = true;

        if (true === $tested) {
            return $stage->dialog->goStage('final');
        }

        return parent::__onStart($stage);
    }

    public function __onFinal(Stage $stage): Navigator
    {
        return $stage
            ->onFallback(function(Dialog $dialog) {
                return $dialog->say()->info('测试结束');
            })
            ->onFallback($this->callContinueTo('final'))
            ->component(new Menu(
                <<<EOF
您可能需要:'
EOF
                ,
                [
                    RasaManagerInt::class,
                    NLUExamplesTask::class,
                    SimpleChatTask::class,
                    '查看介绍' => 'startConversation',
                    '返回' => [Redirector::class, 'fulfill'],
                ]
            ));
    }

    public function __hearing(Hearing $hearing): void
    {
        $hearing->is('b', new ToNext('final'));
    }

    public static function getScripts(): array
    {
        return [
            <<<EOF
测试之前先会有一段介绍. 输入 'b' 跳过.
EOF
            ,

            <<<EOF
多轮对话交互引擎有一个我觉得很优秀的地方: 可以在自己生成的对话中管理自己. 

这个 demo 就用组件化的方式集成了多个自我管理工具.

这些工具理论上只有超级管理员可以使用, 我这里开放了一部分权限供您测试
EOF
            ,
            <<<EOF
自我管理工具比较简便的方式, 是用命令行来做. 现在可用的有:

#matchedIntent : rasa意图管理
#where         : 查看维持多轮对话的关键数据.
#contextRepo   : 查看已注册的 context
#whoami        : 查看用户自己的数据
#runningSpy    : 查看一些关键类的实例数量. 用于排查部分内存泄露问题.

您可以输入指令名来尝试. 
EOF
            ,
            <<<EOF
更高级的功能是用多轮对话本身来管理多轮对话. 现在有三个测试用例:

- rasa.manager         : 直接在对话中查看命中的意图, 匹配率等.
- nlu.examples.manager : 在对话中管理 NLU (自然语言识别单元) 例句, 可添加或删除例句.
- simpleChat.manager   : 直接在对话中 定义默认的意图回复. 
EOF
            ,
            <<<EOF
以上三个用例都和自然语言识别有关, 需要训练机器(通过rasa)才能生效. 由于训练一次要四五个小时, 所以所有改动不会立刻生效.

此外, 我关闭了访客的保存权限 ^_^ . 接下来您可以选择进行测试.
EOF
            ,

        ];
    }



}