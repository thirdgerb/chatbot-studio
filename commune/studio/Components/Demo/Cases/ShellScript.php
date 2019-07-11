<?php


namespace Commune\Studio\Components\Demo\Cases;


use Commune\Chatbot\App\Contexts\ScriptDef;
use Commune\Chatbot\OOHost\Context\Stage;
use Commune\Chatbot\OOHost\Dialogue\Dialog;
use Commune\Chatbot\OOHost\Directing\Navigator;
use Commune\Studio\Components\Demo\Memories\UserStatus;
use Commune\Studio\Components\Demo\Supports\ScriptTrait;

class ShellScript extends ScriptDef
{
    use ScriptTrait;

    const DESCRIPTION = '命令行工具';

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

结束测试必须输入 #cancel 
EOF
)           ->wait()
            ->hearing()
            ->end(function(Dialog $dialog){
                $dialog
                    ->say()
                    ->warning('我关闭了所有响应, 目前只有命令可以使用. 请您尝试 #help,  或者输入 #cancel 退出当前测试.');
                return $dialog->wait();
            });
    }

    public static function getScripts(): array
    {
        return [
            <<<EOF
命令行是程序员最为熟悉的一种对话交互方式. 

它最大的特点是, 程序员可凭借记忆 (或者类似 'fuxx' 这类辅助输入的小程序 ), 直达某一个意图.

EOF
            ,
            <<<EOF
将命令行做到 IM 中, 也存在极大的便利. 这是一种全 IM 平台通用的服务器交互策略. 

开发一套命令, 在所有可运行机器人的 IM 平台上都可以运行. 这对运维人员尤其方便.

不用装APP, 不用打开网页输入复杂账号密码.  直接在微信中就可以使用命令了.

事实上, 本 demo 所有命令都是在 mac 的终端里测试, 然后发布到微信中使用的.  

EOF
            ,

            <<<EOF
其实, 目前自然语言意图识别的技术, 本质上也停留在把一句自然的话语, 解析成一个意图 + 若干参数. 

相当于把一句人话转义成了一个命令行. commune/chatbot 项目对自然语言单元的应用也是本于这个理解.
EOF
            ,
            <<<EOF
commune/chatbot 可以比较快捷地定义各种命令行工具. 而且每套工具可以相互隔离. 

目前的demo 就有两套工具, 一套对用户开放, 以 "#" 作为命令前缀. 另一套用"/"做前缀, 仅对管理员开放. 

接下来进入正式的测试.
EOF
            ,



        ];
    }



}