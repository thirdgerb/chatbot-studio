<?php


namespace Commune\Studio\Components\Demo\Cases;


use Commune\Chatbot\App\Callables\Actions\Redirector;
use Commune\Chatbot\App\Callables\Actions\ToNext;
use Commune\Chatbot\App\Callables\StageComponents\Menu;
use Commune\Chatbot\App\Contexts\ScriptDef;
use Commune\Chatbot\App\Traits\AskContinueTrait;
use Commune\Chatbot\OOHost\Context\Depending;
use Commune\Chatbot\OOHost\Context\Hearing;
use Commune\Chatbot\OOHost\Context\Stage;
use Commune\Chatbot\OOHost\Dialogue\Dialog;
use Commune\Chatbot\OOHost\Directing\Navigator;
use Commune\Demo\App\Cases\Questionnaire\ReadPersonality;
use Commune\Studio\Components\Demo\Memories\UserStatus;
use Commune\Studio\Components\Demo\Supports\ScriptTrait;

class ConversationScript extends ScriptDef
{
    use ScriptTrait, AskContinueTrait;

    const DESCRIPTION = '多轮对话测试用例';

    public static function __depend(Depending $depending): void
    {
    }

    public function __hearing(Hearing $hearing): void
    {
        $hearing->is('b', new ToNext('final'));
    }

    public function __onStart(Stage $stage): Navigator
    {
        $status = UserStatus::from($this);
        $tested = $status->testConversation;
        $status->testConversation = true;

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
由于目前 demo 所有的对话都是多轮对话本身, 所以目前仅准备了一个例子.
演示如何用多轮对话实现复杂的问卷调查 (非线性, 有计算逻辑, 有历史记忆, 可以重置, 生产级应用场景). 
            
您可能需要:
EOF
            ,
            [
                '问卷调查模拟:15秒读懂您的性格' => ReadPersonality::class,
                '查看介绍' => 'startConversation',
                '返回' => [Redirector::class, 'fulfill'],
            ]
        ));
    }

    public static function getScripts(): array
    {
        return [
            <<<EOF
以下开始多轮对话的介绍. 输入'b'可结束介绍.
EOF
            ,
            <<<EOF
常见的对话机器人, 在对话管理方面有两大类型:

- 用户主导
- 机器人主导

涉及对话的复杂程度, 又表现为:

- 单轮对话
- 多轮对话
- 复杂多轮对话

从对话的用途来分, 又分为:

- 闲聊对话 (用户主导)
- 任务型对话 (用户开启, 机器人主导)
- 通知类对话 (机器人开启对话)

EOF
            ,
            <<<EOF
单轮对话, 指用户任何一句输入, 都在同一个开放域中识别意图. 下一句不需要知道上一句是什么.

目前主流的智能音箱, siri等助理, 其实都还是单轮对话.  

而多轮对话, 指上下文相关的对话内容. 所谓的上下文相关, 我设计的模型包含以下几个 feature:

1. 上下文记忆 : 这句话里得到的关键信息, 后面的对话都记得.
2. 作用域变化 : 一句话在A语境意思是 a, 到了 B语境意思变成 b, 处理逻辑也要改变.
3. 语境切换 : 从 A 语境切换到 B , 完成 B 语境再回转到 A. 您现在看到的就是例子.
4. 语境脱出 : 主动跳出某个语境, 比如用户想终止任务, 或是临时想插一个别的问题, 或是出错等.
5. 语境挂起与唤醒 : 比如用户点菜, 要5分钟后才能送过来. 这时语境挂起, 用户可以先聊别的需求.
6. 多语境多任务 : 用户通过对话同时处理多个任务, 像操作系统一样, 可以来回切换. 

EOF
            ,
            <<<EOF
多轮对话 "简单" 与 "复杂" 的区别, 主要体现在工程性上. 

目前我看到的一些多轮对话机器人, 实现feature有限, 另外在工程性上较弱, 不能实现复杂工程需求:

- 可编程 : 以对话为目标的机器人, 可能连"参数校验" 都很难实现
- 组件化 : 逻辑可以复用, 可以重写, 可以组装
- 可配置化 : 任何成熟的功能可以用配置取代代码 ( 大公司做的的多轮对话图形化编辑就比较厉害 )
- 工业级可用 : 可监控, 高可用, 容错, 弹性扩容, 可迁移

EOF
            ,
            <<<EOF
commune/chatbot 项目, 就是针对以上 feature 设计开发的. 您现在看到的就是实际的多轮对话效果. 
EOF

        ];
    }




}