<?php


namespace Commune\Studio\Components\Demo\Cases;


use Commune\Chatbot\App\Callables\Actions\Redirector;
use Commune\Chatbot\App\Callables\Actions\ToNext;
use Commune\Chatbot\App\Contexts\ScriptDef;
use Commune\Chatbot\OOHost\Context\Stage;
use Commune\Chatbot\OOHost\Dialogue\Dialog;
use Commune\Chatbot\OOHost\Directing\Navigator;
use Commune\Studio\Components\Demo\Memories\UserStatus;
use Commune\Studio\Components\Demo\Supports\ScriptTrait;

class ShellScript extends ScriptDef
{
    use ScriptTrait;

    const DESCRIPTION = '命令行工具测试';

    public function __onStart(Stage $stage): Navigator
    {
        $status = UserStatus::from($this);
        $tested = $status->testShell;
        $status->testShell = true;

        if (true === $tested) {
            return $stage->dialog->goStage('final');
        }

        return parent::__onStart($stage);
    }

    public function __onFinal(Stage $stage): Navigator
    {
        return $stage->buildTalk()
            ->info(<<<EOF
请输入 #help , 可以查看目前可用的命令. 推荐以下命令:

#help       : 查看当前可用的命令 
#runningSpy : 查看本 demo 的运行情况, 主要用于排查内存泄露
#whoami     : 查看用户自身的数据
#where -h   : 查看 demo 目前缓存的上下文信息 ( commune/chatbot 的核心绝密技术! 手动狗头)

EOF
)           ->askChoose(
        '您可能还需要:',
                [
                    '查看介绍',
                    '查看源码',
                    '返回',
                ]
            )
            ->wait()
            ->hearing()
            ->is('b', [Redirector::class, 'fulfill'])
            ->isChoice(0, new ToNext('startConversation'))
            ->isChoice(1, new ToNext('source'))
            ->isChoice(2, [Redirector::class, 'fulfill'])
            ->end(function(Dialog $dialog){
                $dialog
                    ->say()
                    ->warning('请您尝试 #help 查看可用命令.  输入 #cancel 或者"b" 退出当前测试.');
                return $dialog->wait();
            });
    }


    public function __onSource(Stage $stage) : Navigator
    {
        return $stage->buildTalk()
            ->info('源码在: https://github.com/thirdgerb/chatbot-studio/blob/develop/commune/studio/Components/Demo/Cases/ShellScript.php')
            ->goStage('final');
    }

    public static function getScripts(): array
    {
        return [
            <<<EOF
命令行是程序员最熟悉的一种交互方式. 而全平台的对话机器人, 可以把命令行带到任何一个IM中.

github 自己火爆的机器人项目 hubot 就主要用于在IM中直接运维服务器. 

commune/chatbot 项目则对命令行做了完整的支持.
EOF

            ,
            <<<EOF
将命令行做到 IM 中, 也存在极大的便利: 这是一种全 IM 平台通用的服务器交互策略.

不用装APP, 不用打开网页输入复杂账号密码.  直接在微信中就可以使用命令了.

本 demo 所有命令都是在 mac 的终端里测试, 然后发布到微信中使用的.

EOF
            ,

            <<<EOF
目前自然语言意图识别的技术, 本质上也停留在把一句自然的话语, 解析成一个意图 + 若干参数.

相当于把一句人话转义成了一个命令行. commune/chatbot 项目对自然语言单元的应用也是本于这个理解.
EOF
            ,
            <<<EOF
commune/chatbot 可以比较快捷地定义各种命令行工具. 而且每套工具可以相互隔离.

目前的demo 就有两套工具, 一套对用户开放, 以 "#" 作为命令前缀. 另一套用"/"做前缀, 仅对管理员开放.
EOF
            ,



        ];
    }



}